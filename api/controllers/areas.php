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
require_once '../models/Area.php';

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
    // Instancia la base de datos y el modelo de area
    $database = new Database();
    $db = $database->getConnection();
    
    // Verifica si la conexión fue exitosa
    if (!$db) {
        sendResponse(500, array("message" => "Error de conexión a la base de datos"));
    }
    
    $area = new Area($db);
    
} catch (Exception $e) {
    error_log("Error al inicializar conexión: " . $e->getMessage());
    sendResponse(500, array("message" => "Error interno del servidor"));
}

switch ($request_method) {
    case 'GET':
        try {
            // Verificar si se solicitan las sedes para el selector
            if (isset($_GET['action']) && $_GET['action'] === 'get_sedes') {
                $stmt = $area->getSedes();
                
                if ($stmt === false) {
                    sendResponse(500, array("message" => "Error al consultar las sedes"));
                }
                
                $sedes = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $sedes[] = array(
                        "id_sede" => intval($row['id_sede']),
                        "nombre_sede" => $row['nombre_sede']
                    );
                }
                
                sendResponse(200, array("sedes" => $sedes));
            }
            
            // Verificar si se filtran areas por sede
            if (isset($_GET['id_sede']) && !empty($_GET['id_sede'])) {
                $stmt = $area->readBySede(intval($_GET['id_sede']));
                
                if ($stmt === false) {
                    sendResponse(500, array("message" => "Error al consultar las areas"));
                }
                
                $areas_arr = array();
                $areas_arr["records"] = array();
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $area_item = array(
                        "id_area" => intval($row['id_area']),
                        "nombre_area" => $row['nombre_area'],
                        "id_sede" => intval($row['id_sede'])
                    );
                    array_push($areas_arr["records"], $area_item);
                }
                
                $areas_arr["total"] = count($areas_arr["records"]);
                sendResponse(200, $areas_arr);
            }
            
            // Lee todas las areas con información de sede
            $stmt = $area->read();
            
            // Verificar si la consulta falló
            if ($stmt === false) {
                sendResponse(500, array("message" => "Error al consultar las areas"));
            }
            
            $num = $stmt->rowCount();

            if ($num > 0) {
                $areas_arr = array();
                $areas_arr["records"] = array();
                $areas_arr["total"] = $num;

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $area_item = array(
                        "id_area" => intval($row['id_area']),
                        "nombre_area" => $row['nombre_area'],
                        "id_sede" => intval($row['id_sede']),
                        "nombre_sede" => $row['nombre_sede'] ?? ''
                    );
                    array_push($areas_arr["records"], $area_item);
                }

                sendResponse(200, $areas_arr);
            } else {
                sendResponse(200, array(
                    "records" => array(),
                    "total" => 0,
                    "message" => "No se encontraron areas."
                ));
            }
        } catch (Exception $e) {
            error_log("Error en GET areas: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al obtener las areas"));
        }
        break;

    case 'POST':
        try {
            // Crea una nueva area
            $data = getJsonInput();

            // Validaciones
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            if (empty($data->nombre_area) || trim($data->nombre_area) === '') {
                sendResponse(400, array("message" => "El nombre del area es requerido"));
            }

            if (empty($data->id_sede) || !is_numeric($data->id_sede)) {
                sendResponse(400, array("message" => "La sede es requerida"));
            }

            // Validar longitud del nombre
            if (strlen(trim($data->nombre_area)) > 100) {
                sendResponse(400, array("message" => "El nombre del area no puede exceder 100 caracteres"));
            }

            // Verificar si ya existe un area con ese nombre en la misma sede
            if (method_exists($area, 'existeNombre') && $area->existeNombre(trim($data->nombre_area), $data->id_sede)) {
                sendResponse(409, array("message" => "Ya existe un area con ese nombre en esta sede"));
            }

            $area->nombre_area = trim($data->nombre_area);
            $area->id_sede = intval($data->id_sede);

            if ($area->create()) {
                sendResponse(201, array(
                    "message" => "El area ha sido creada exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo crear el area. Inténtelo nuevamente."));
            }
        } catch (Exception $e) {
            error_log("Error en POST areas: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al crear el area"));
        }
        break;

    case 'PUT':
        try {
            // Actualiza un area existente
            $data = getJsonInput();

            // Validaciones
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            if (empty($data->id_area) || !is_numeric($data->id_area)) {
                sendResponse(400, array("message" => "ID de area inválido"));
            }

            if (empty($data->nombre_area) || trim($data->nombre_area) === '') {
                sendResponse(400, array("message" => "El nombre del area es requerido"));
            }

            if (empty($data->id_sede) || !is_numeric($data->id_sede)) {
                sendResponse(400, array("message" => "La sede es requerida"));
            }

            // Validar longitud del nombre
            if (strlen(trim($data->nombre_area)) > 100) {
                sendResponse(400, array("message" => "El nombre del area no puede exceder 100 caracteres"));
            }

            // Verificar si ya existe otra area con ese nombre en la misma sede
            if (method_exists($area, 'existeNombre') && $area->existeNombre(trim($data->nombre_area), $data->id_sede, $data->id_area)) {
                sendResponse(409, array("message" => "Ya existe otra area con ese nombre en esta sede"));
            }

            $area->id_area = intval($data->id_area);
            $area->nombre_area = trim($data->nombre_area);
            $area->id_sede = intval($data->id_sede);

            if ($area->update()) {
                sendResponse(200, array(
                    "message" => "El area ha sido actualizada exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo actualizar el area. Verifique que existe."));
            }
        } catch (Exception $e) {
            error_log("Error en PUT areas: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al actualizar el area"));
        }
        break;

    case 'DELETE':
        try {
            // Elimina un area
            $data = getJsonInput();

            // Validaciones
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            if (empty($data->id_area) || !is_numeric($data->id_area)) {
                sendResponse(400, array("message" => "ID de area inválido"));
            }

            $area->id_area = intval($data->id_area);

            if ($area->delete()) {
                sendResponse(200, array(
                    "message" => "El area ha sido eliminada exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo eliminar el area. Verifique que existe."));
            }
        } catch (Exception $e) {
            error_log("Error en DELETE areas: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al eliminar el area"));
        }
        break;

    default:
        // Petición no soportada
        sendResponse(405, array("message" => "Método no permitido"));
        break;
}
?>