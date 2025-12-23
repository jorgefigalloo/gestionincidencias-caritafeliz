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
require_once __DIR__ . '/../models/RolUsuario.php';

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
    // Instancia la base de datos y el modelo de rol usuario
    $database = new Database();
    $db = $database->getConnection();
    
    // Verifica si la conexión fue exitosa
    if (!$db) {
        sendResponse(500, array("message" => "Error de conexión a la base de datos"));
    }
    
    $rolUsuario = new RolUsuario($db);
    
} catch (Exception $e) {
    error_log("Error al inicializar conexión: " . $e->getMessage());
    sendResponse(500, array("message" => "Error interno del servidor"));
}

switch ($request_method) {
    case 'GET':
        try {
            // Verificar si se solicitan estadísticas de uso
            if (isset($_GET['action']) && $_GET['action'] === 'stats') {
                $stats = $rolUsuario->getUsageStats();
                sendResponse(200, array("stats" => $stats));
            }
            
            // Lee todos los roles de usuario
            $stmt = $rolUsuario->read();
            
            // Verificar si la consulta falló
            if ($stmt === false) {
                sendResponse(500, array("message" => "Error al consultar los roles"));
            }
            
            $num = $stmt->rowCount();

            if ($num > 0) {
                $roles_arr = array();
                $roles_arr["records"] = array();
                $roles_arr["total"] = $num;

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $rol_item = array(
                        "id_rol" => intval($row['id_rol']),
                        "nombre_rol" => $row['nombre_rol']
                    );
                    array_push($roles_arr["records"], $rol_item);
                }

                sendResponse(200, $roles_arr);
            } else {
                sendResponse(200, array(
                    "records" => array(),
                    "total" => 0,
                    "message" => "No se encontraron roles."
                ));
            }
        } catch (Exception $e) {
            error_log("Error en GET roles: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al obtener los roles"));
        }
        break;

    case 'POST':
        try {
            // Crea un nuevo rol
            $data = getJsonInput();

            // Validaciones
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            if (empty($data->nombre_rol) || trim($data->nombre_rol) === '') {
                sendResponse(400, array("message" => "El nombre del rol es requerido"));
            }

            // Validar formato del nombre del rol
            $validation = $rolUsuario->validateRoleName($data->nombre_rol);
            if (!$validation['valid']) {
                sendResponse(400, array("message" => implode(', ', $validation['errors'])));
            }

            // Verificar si ya existe un rol con ese nombre
            if (method_exists($rolUsuario, 'existeNombre') && $rolUsuario->existeNombre(trim($data->nombre_rol))) {
                sendResponse(409, array("message" => "Ya existe un rol con ese nombre"));
            }

            $rolUsuario->nombre_rol = trim(strtolower($data->nombre_rol));

            if ($rolUsuario->create()) {
                sendResponse(201, array(
                    "message" => "El rol ha sido creado exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo crear el rol. Inténtelo nuevamente."));
            }
        } catch (Exception $e) {
            error_log("Error en POST roles: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al crear el rol"));
        }
        break;

    case 'PUT':
        try {
            // Actualiza un rol existente
            $data = getJsonInput();

            // Validaciones
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            if (empty($data->id_rol) || !is_numeric($data->id_rol)) {
                sendResponse(400, array("message" => "ID de rol inválido"));
            }

            if (empty($data->nombre_rol) || trim($data->nombre_rol) === '') {
                sendResponse(400, array("message" => "El nombre del rol es requerido"));
            }

            // Validar formato del nombre del rol
            $validation = $rolUsuario->validateRoleName($data->nombre_rol);
            if (!$validation['valid']) {
                sendResponse(400, array("message" => implode(', ', $validation['errors'])));
            }

            // Verificar si ya existe otro rol con ese nombre
            if (method_exists($rolUsuario, 'existeNombre') && $rolUsuario->existeNombre(trim($data->nombre_rol), $data->id_rol)) {
                sendResponse(409, array("message" => "Ya existe otro rol con ese nombre"));
            }

            $rolUsuario->id_rol = intval($data->id_rol);
            $rolUsuario->nombre_rol = trim(strtolower($data->nombre_rol));

            if ($rolUsuario->update()) {
                sendResponse(200, array(
                    "message" => "El rol ha sido actualizado exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo actualizar el rol. Verifique que existe."));
            }
        } catch (Exception $e) {
            error_log("Error en PUT roles: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al actualizar el rol"));
        }
        break;

    case 'DELETE':
        try {
            // Elimina un rol
            $data = getJsonInput();

            // Validaciones
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            if (empty($data->id_rol) || !is_numeric($data->id_rol)) {
                sendResponse(400, array("message" => "ID de rol inválido"));
            }

            $rolUsuario->id_rol = intval($data->id_rol);

            // Verificar si el rol está siendo usado
            if ($rolUsuario->isInUse()) {
                sendResponse(409, array(
                    "message" => "No se puede eliminar el rol porque está siendo usado por uno o más usuarios.",
                    "code" => "ROL_IN_USE"
                ));
            }

            if ($rolUsuario->delete()) {
                sendResponse(200, array(
                    "message" => "El rol ha sido eliminado exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo eliminar el rol. Verifique que existe."));
            }
        } catch (Exception $e) {
            error_log("Error en DELETE roles: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al eliminar el rol"));
        }
        break;

    default:
        // Petición no soportada
        sendResponse(405, array("message" => "Método no permitido"));
        break;
}
?>