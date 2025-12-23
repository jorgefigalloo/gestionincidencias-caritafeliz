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
require_once __DIR__ . '/../models/database.php';
require_once __DIR__ . '/../models/TipoIncidencia.php';

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
    // Instancia la base de datos y el modelo de tipo incidencia
    $database = new Database();
    $db = $database->getConnection();
    
    // Verifica si la conexión fue exitosa
    if (!$db) {
        sendResponse(500, array("message" => "Error de conexión a la base de datos"));
    }
    
    $tipoIncidencia = new TipoIncidencia($db);
    
} catch (Exception $e) {
    error_log("Error al inicializar conexión: " . $e->getMessage());
    sendResponse(500, array("message" => "Error interno del servidor"));
}

switch ($request_method) {
    case 'GET':
        try {
            // Verificar si se solicitan estadísticas de uso
            if (isset($_GET['action']) && $_GET['action'] === 'stats') {
                $stats = $tipoIncidencia->getUsageStats();
                sendResponse(200, array("stats" => $stats));
            }
            
            // Lee todos los tipos de incidencia
            $stmt = $tipoIncidencia->read();
            
            // Verificar si la consulta falló
            if ($stmt === false) {
                sendResponse(500, array("message" => "Error al consultar los tipos de incidencia"));
            }
            
            $num = $stmt->rowCount();

            if ($num > 0) {
                $tipos_arr = array();
                $tipos_arr["records"] = array();
                $tipos_arr["total"] = $num;

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $tipo_item = array(
                        "id_tipo_incidencia" => intval($row['id_tipo_incidencia']),
                        "nombre" => $row['nombre']
                    );
                    array_push($tipos_arr["records"], $tipo_item);
                }

                sendResponse(200, $tipos_arr);
            } else {
                sendResponse(200, array(
                    "records" => array(),
                    "total" => 0,
                    "message" => "No se encontraron tipos de incidencia."
                ));
            }
        } catch (Exception $e) {
            error_log("Error en GET tipos de incidencia: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al obtener los tipos de incidencia"));
        }
        break;

    case 'POST':
        try {
            // Crea un nuevo tipo
            $data = getJsonInput();

            // Validaciones
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            if (empty($data->nombre) || trim($data->nombre) === '') {
                sendResponse(400, array("message" => "El nombre del tipo de incidencia es requerido"));
            }

            // Validar longitud del nombre
            if (strlen(trim($data->nombre)) > 50) {
                sendResponse(400, array("message" => "El nombre no puede exceder 50 caracteres"));
            }

            // Verificar si ya existe un tipo con ese nombre
            if (method_exists($tipoIncidencia, 'existeNombre') && $tipoIncidencia->existeNombre(trim($data->nombre))) {
                sendResponse(409, array("message" => "Ya existe un tipo de incidencia con ese nombre"));
            }

            $tipoIncidencia->nombre = trim($data->nombre);

            if ($tipoIncidencia->create()) {
                sendResponse(201, array(
                    "message" => "El tipo de incidencia ha sido creado exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo crear el tipo de incidencia. Inténtelo nuevamente."));
            }
        } catch (Exception $e) {
            error_log("Error en POST tipos de incidencia: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al crear el tipo de incidencia"));
        }
        break;

    case 'PUT':
        try {
            // Actualiza un tipo existente
            $data = getJsonInput();

            // Validaciones
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            if (empty($data->id_tipo_incidencia) || !is_numeric($data->id_tipo_incidencia)) {
                sendResponse(400, array("message" => "ID de tipo de incidencia inválido"));
            }

            if (empty($data->nombre) || trim($data->nombre) === '') {
                sendResponse(400, array("message" => "El nombre del tipo de incidencia es requerido"));
            }

            // Validar longitud del nombre
            if (strlen(trim($data->nombre)) > 50) {
                sendResponse(400, array("message" => "El nombre no puede exceder 50 caracteres"));
            }

            // Verificar si ya existe otro tipo con ese nombre
            if (method_exists($tipoIncidencia, 'existeNombre') && $tipoIncidencia->existeNombre(trim($data->nombre), $data->id_tipo_incidencia)) {
                sendResponse(409, array("message" => "Ya existe otro tipo de incidencia con ese nombre"));
            }

            $tipoIncidencia->id_tipo_incidencia = intval($data->id_tipo_incidencia);
            $tipoIncidencia->nombre = trim($data->nombre);

            if ($tipoIncidencia->update()) {
                sendResponse(200, array(
                    "message" => "El tipo de incidencia ha sido actualizado exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo actualizar el tipo de incidencia. Verifique que existe."));
            }
        } catch (Exception $e) {
            error_log("Error en PUT tipos de incidencia: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al actualizar el tipo de incidencia"));
        }
        break;

    case 'DELETE':
        try {
            // Elimina un tipo
            $data = getJsonInput();

            // Validaciones
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            if (empty($data->id_tipo_incidencia) || !is_numeric($data->id_tipo_incidencia)) {
                sendResponse(400, array("message" => "ID de tipo de incidencia inválido"));
            }

            $tipoIncidencia->id_tipo_incidencia = intval($data->id_tipo_incidencia);

            // Verificar si el tipo está siendo usado
            if ($tipoIncidencia->isInUse()) {
                sendResponse(409, array(
                    "message" => "No se puede eliminar el tipo porque está siendo usado por una o más incidencias.",
                    "code" => "TIPO_IN_USE"
                ));
            }

            if ($tipoIncidencia->delete()) {
                sendResponse(200, array(
                    "message" => "El tipo de incidencia ha sido eliminado exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo eliminar el tipo de incidencia. Verifique que existe."));
            }
        } catch (Exception $e) {
            error_log("Error en DELETE tipos de incidencia: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al eliminar el tipo de incidencia"));
        }
        break;

    default:
        // Petición no soportada
        sendResponse(405, array("message" => "Método no permitido"));
        break;
}
?>