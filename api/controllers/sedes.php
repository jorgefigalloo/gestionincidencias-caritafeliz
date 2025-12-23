<?php
// Configura los encabezados para permitir solicitudes desde cualquier origen
// y para asegurar que la respuesta sea tratada como JSON.
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Maneja las solicitudes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluye los archivos de modelo y de conexión a la base de datos.
require_once '../models/database.php';
require_once '../models/Sede.php';

// Función para enviar respuesta JSON y terminar el script
function sendResponse($code, $data) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// Función para validar JSON
function getJsonInput() {
    $input = file_get_contents("php://input");
    $data = json_decode($input);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendResponse(400, array("message" => "JSON inválido: " . json_last_error_msg()));
    }
    
    return $data;
}

// Obtén el método de la petición
$request_method = $_SERVER["REQUEST_METHOD"];

try {
    // Instancia la base de datos y el modelo de sede
    $database = new Database();
    $db = $database->getConnection();
    
    // Verifica si la conexión fue exitosa
    if (!$db) {
        sendResponse(500, array("message" => "Error de conexión a la base de datos"));
    }
    
    $sede = new Sede($db);
    
} catch (Exception $e) {
    error_log("Error al inicializar conexión: " . $e->getMessage());
    sendResponse(500, array("message" => "Error interno del servidor"));
}

switch ($request_method) {
    case 'GET':
        try {
            // Lee todas las sedes
            $stmt = $sede->read();
            
            // Verificar si la consulta falló
            if ($stmt === false) {
                sendResponse(500, array("message" => "Error al consultar las sedes"));
            }
            
            $num = $stmt->rowCount();

            if ($num > 0) {
                $sedes_arr = array();
                $sedes_arr["records"] = array();
                $sedes_arr["total"] = $num;

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Mapea 'nombre_sede' de la base de datos a 'nombre' para el frontend
                    $sede_item = array(
                        "id_sede" => intval($row['id_sede']),
                        "nombre" => $row['nombre_sede'],
                        "descripcion" => html_entity_decode($row['descripcion'] ?? '')
                    );
                    array_push($sedes_arr["records"], $sede_item);
                }

                sendResponse(200, $sedes_arr);
            } else {
                sendResponse(200, array(
                    "records" => array(),
                    "total" => 0,
                    "message" => "No se encontraron sedes."
                ));
            }
        } catch (Exception $e) {
            error_log("Error en GET sedes: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al obtener las sedes"));
        }
        break;

    case 'POST':
        try {
            // Crea una nueva sede
            $data = getJsonInput();

            // Validaciones
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            if (empty($data->nombre) || trim($data->nombre) === '') {
                sendResponse(400, array("message" => "El nombre de la sede es requerido"));
            }

            // Validar longitud del nombre
            if (strlen(trim($data->nombre)) > 50) {
                sendResponse(400, array("message" => "El nombre de la sede no puede exceder 50 caracteres"));
            }

            // Verificar si ya existe una sede con ese nombre
            if (method_exists($sede, 'existeNombre') && $sede->existeNombre(trim($data->nombre))) {
                sendResponse(409, array("message" => "Ya existe una sede con ese nombre"));
            }

            $sede->nombre_sede = trim($data->nombre);
            $sede->descripcion = isset($data->descripcion) ? trim($data->descripcion) : '';

            if ($sede->create()) {
                sendResponse(201, array(
                    "message" => "La sede ha sido creada exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo crear la sede. Inténtelo nuevamente."));
            }
        } catch (Exception $e) {
            error_log("Error en POST sedes: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al crear la sede"));
        }
        break;

    case 'PUT':
        try {
            // Actualiza una sede existente
            $data = getJsonInput();

            // Validaciones
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            if (empty($data->id_sede) || !is_numeric($data->id_sede)) {
                sendResponse(400, array("message" => "ID de sede inválido"));
            }

            if (empty($data->nombre) || trim($data->nombre) === '') {
                sendResponse(400, array("message" => "El nombre de la sede es requerido"));
            }

            // Validar longitud del nombre
            if (strlen(trim($data->nombre)) > 50) {
                sendResponse(400, array("message" => "El nombre de la sede no puede exceder 50 caracteres"));
            }

            // Verificar si ya existe otra sede con ese nombre
            if (method_exists($sede, 'existeNombre') && $sede->existeNombre(trim($data->nombre), $data->id_sede)) {
                sendResponse(409, array("message" => "Ya existe otra sede con ese nombre"));
            }

            $sede->id_sede = intval($data->id_sede);
            $sede->nombre_sede = trim($data->nombre);
            $sede->descripcion = isset($data->descripcion) ? trim($data->descripcion) : '';

            if ($sede->update()) {
                sendResponse(200, array(
                    "message" => "La sede ha sido actualizada exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo actualizar la sede. Verifique que existe."));
            }
        } catch (Exception $e) {
            error_log("Error en PUT sedes: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al actualizar la sede"));
        }
        break;

    case 'DELETE':
        try {
            // Elimina una sede
            $data = getJsonInput();

            // Validaciones
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            if (empty($data->id_sede) || !is_numeric($data->id_sede)) {
                sendResponse(400, array("message" => "ID de sede inválido"));
            }

            $sede->id_sede = intval($data->id_sede);

            if ($sede->delete()) {
                sendResponse(200, array(
                    "message" => "La sede ha sido eliminada exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo eliminar la sede. Verifique que existe."));
            }
        } catch (Exception $e) {
            error_log("Error en DELETE sedes: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al eliminar la sede"));
        }
        break;

    default:
        // Petición no soportada
        sendResponse(405, array("message" => "Método no permitido"));
        break;
}
?>