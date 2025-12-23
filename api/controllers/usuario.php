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
require_once __DIR__ . '/../models/Usuario.php';

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
    // Instancia la base de datos y el modelo de usuario
    $database = new Database();
    $db = $database->getConnection();
    
    // Verifica si la conexión fue exitosa
    if (!$db) {
        sendResponse(500, array("message" => "Error de conexión a la base de datos"));
    }
    
    $usuario = new Usuario($db);
    
} catch (Exception $e) {
    error_log("Error al inicializar conexión: " . $e->getMessage());
    sendResponse(500, array("message" => "Error interno del servidor"));
}

switch ($request_method) {
    case 'GET':
        try {
            // Verificar si se solicitan roles para el selector
            if (isset($_GET['action']) && $_GET['action'] === 'get_roles') {
                $stmt = $usuario->getRoles();
                
                if ($stmt === false) {
                    sendResponse(500, array("message" => "Error al consultar los roles"));
                }
                
                $roles = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $roles[] = array(
                        "id_rol" => intval($row['id_rol']),
                        "nombre_rol" => $row['nombre_rol']
                    );
                }
                
                sendResponse(200, array("roles" => $roles));
            }

            // Verificar si se solicitan áreas para el selector
            if (isset($_GET['action']) && $_GET['action'] === 'get_areas') {
                $stmt = $usuario->getAreas();
                
                if ($stmt === false) {
                    sendResponse(500, array("message" => "Error al consultar las áreas"));
                }
                
                $areas = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $areas[] = array(
                        "id_area" => intval($row['id_area']),
                        "nombre_area" => $row['nombre_area'],
                        "nombre_sede" => $row['nombre_sede'],
                        "area_completa" => $row['area_completa']
                    );
                }
                
                sendResponse(200, array("areas" => $areas));
            }

            // Verificar si se solicitan estadísticas
            if (isset($_GET['action']) && $_GET['action'] === 'stats') {
                $stats = $usuario->getStatsPerRole();
                sendResponse(200, array("stats" => $stats));
            }
            
            // Lee todos los usuarios
            $stmt = $usuario->read();
            
            // Verificar si la consulta falló
            if ($stmt === false) {
                sendResponse(500, array("message" => "Error al consultar los usuarios"));
            }
            
            $num = $stmt->rowCount();

            if ($num > 0) {
                $usuarios_arr = array();
                $usuarios_arr["records"] = array();
                $usuarios_arr["total"] = $num;

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $usuario_item = array(
                        "id_usuario" => intval($row['id_usuario']),
                        "nombre_completo" => $row['nombre_completo'],
                        "email" => $row['email'] ?? '',
                        "notificaciones_activas" => intval($row['notificaciones_activas'] ?? 1),
                        "username" => $row['username'],
                        "ID_ROL_USUARIO" => intval($row['ID_ROL_USUARIO']),
                        "id_area" => $row['id_area'] ? intval($row['id_area']) : null,
                        "estado" => $row['estado'],
                        "nombre_rol" => $row['nombre_rol'] ?? '',
                        "nombre_area" => $row['nombre_area'] ?? '',
                        "nombre_sede" => $row['nombre_sede'] ?? ''
                    );
                    array_push($usuarios_arr["records"], $usuario_item);
                }

                sendResponse(200, $usuarios_arr);
            } else {
                sendResponse(200, array(
                    "records" => array(),
                    "total" => 0,
                    "message" => "No se encontraron usuarios."
                ));
            }
        } catch (Exception $e) {
            error_log("Error en GET usuarios: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al obtener los usuarios"));
        }
        break;

    case 'POST':
        try {
            $data = getJsonInput();

            // Validaciones
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            // Manejar login - VERSIÓN FINAL CORREGIDA
            if (isset($data->action) && $data->action === 'login') {
                if (empty($data->username) || empty($data->password)) {
                    sendResponse(400, array("message" => "Username y password son requeridos"));
                }

                try {
                    $query = "SELECT u.id_usuario, u.nombre_completo, u.username, u.estado, 
                                    r.nombre_rol, a.nombre_area, s.nombre_sede, u.id_area
                            FROM usuarios u 
                            INNER JOIN rol_usuario r ON u.ID_ROL_USUARIO = r.id_rol
                            LEFT JOIN areas a ON u.id_area = a.id_area
                            LEFT JOIN sedes s ON a.id_sede = s.id_sede
                            WHERE u.username = ? AND u.password = ? AND u.estado = 'activo'";
                    
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(1, $data->username, PDO::PARAM_STR);
                    $stmt->bindParam(2, $data->password, PDO::PARAM_STR);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0) {
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        // Iniciar sesión PHP
                        session_start();
                        $_SESSION['user_id'] = $user['id_usuario'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['rol'] = $user['nombre_rol'];
                        $_SESSION['nombre_completo'] = $user['nombre_completo'];
                        $_SESSION['id_area'] = $user['id_area'];
                        
                        // TODOS LOS ALIAS NECESARIOS para compatibilidad total
                        $sessionData = array(
                            // IDs
                            "id_usuario" => intval($user['id_usuario']),
                            "user_id" => intval($user['id_usuario']),
                            "id" => intval($user['id_usuario']),
                            
                            // Nombres - ESTO ES LO IMPORTANTE
                            "nombre_completo" => $user['nombre_completo'],  // Para dashboard
                            "nombre" => $user['nombre_completo'],           // Para index
                            "username" => $user['username'],
                            
                            // Rol
                            "rol" => $user['nombre_rol'],
                            
                            // Ubicación
                            "id_area" => $user['id_area'] ? intval($user['id_area']) : null,
                            "nombre_area" => $user['nombre_area'],
                            "nombre_sede" => $user['nombre_sede'],
                            "area" => $user['nombre_area'],
                            "sede" => $user['nombre_sede'],
                            
                            // Timestamp
                            "loginTime" => time() * 1000
                        );
                        
                        // Redirección según rol
                        $redirectUrl = 'index.php';
                        if ($user['nombre_rol'] === 'admin' || $user['nombre_rol'] === 'tecnico') {
                            $redirectUrl = 'dashboard.php';
                        }
                        
                        sendResponse(200, array(
                            "success" => true,
                            "message" => "Login exitoso",
                            "user" => $sessionData,
                            "redirect" => $redirectUrl
                        ));
                    } else {
                        sendResponse(401, array(
                            "success" => false,
                            "message" => "Credenciales incorrectas o usuario inactivo"
                        ));
                    }
                } catch (Exception $e) {
                    error_log("Error en login: " . $e->getMessage());
                    sendResponse(500, array(
                        "success" => false,
                        "message" => "Error interno en el proceso de autenticación"
                    ));
                }
                break;
            }

            // Crear usuario (lógica original)
            if (empty($data->nombre_completo) || trim($data->nombre_completo) === '') {
                sendResponse(400, array("message" => "El nombre completo es requerido"));
            }

            if (empty($data->username) || trim($data->username) === '') {
                sendResponse(400, array("message" => "El username es requerido"));
            }

            if (empty($data->password) || trim($data->password) === '') {
                sendResponse(400, array("message" => "La contraseña es requerida"));
            }

            if (empty($data->ID_ROL_USUARIO) || !is_numeric($data->ID_ROL_USUARIO)) {
                sendResponse(400, array("message" => "El rol es requerido"));
            }

            // Validar longitudes
            if (strlen(trim($data->nombre_completo)) > 100) {
                sendResponse(400, array("message" => "El nombre completo no puede exceder 100 caracteres"));
            }

            if (strlen(trim($data->username)) > 50) {
                sendResponse(400, array("message" => "El username no puede exceder 50 caracteres"));
            }

            // Verificar si ya existe un usuario con ese username
            if ($usuario->existeUsername(trim($data->username))) {
                sendResponse(409, array("message" => "Ya existe un usuario con ese username"));
            }

            // Validar email si se proporciona
            if (!empty($data->email) && !filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
                sendResponse(400, array("message" => "El email proporcionado no es válido"));
            }

            $usuario->nombre_completo = trim($data->nombre_completo);
            $usuario->email = !empty($data->email) ? trim($data->email) : null;
            $usuario->notificaciones_activas = isset($data->notificaciones_activas) ? intval($data->notificaciones_activas) : 1;
            $usuario->username = trim($data->username);
            $usuario->password = trim($data->password);
            $usuario->ID_ROL_USUARIO = intval($data->ID_ROL_USUARIO);
            $usuario->id_area = !empty($data->id_area) ? intval($data->id_area) : null;
            $usuario->estado = isset($data->estado) ? $data->estado : 'activo';

            if ($usuario->create()) {
                sendResponse(201, array(
                    "message" => "El usuario ha sido creado exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo crear el usuario. Inténtelo nuevamente."));
            }
        } catch (Exception $e) {
            error_log("Error en POST usuarios: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al procesar la solicitud"));
        }
        break;

    case 'PUT':
        try {
            $data = getJsonInput();

            // Validaciones
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            if (empty($data->id_usuario) || !is_numeric($data->id_usuario)) {
                sendResponse(400, array("message" => "ID de usuario inválido"));
            }

            if (empty($data->nombre_completo) || trim($data->nombre_completo) === '') {
                sendResponse(400, array("message" => "El nombre completo es requerido"));
            }

            if (empty($data->username) || trim($data->username) === '') {
                sendResponse(400, array("message" => "El username es requerido"));
            }

            if (empty($data->ID_ROL_USUARIO) || !is_numeric($data->ID_ROL_USUARIO)) {
                sendResponse(400, array("message" => "El rol es requerido"));
            }

            // Validar longitudes
            if (strlen(trim($data->nombre_completo)) > 100) {
                sendResponse(400, array("message" => "El nombre completo no puede exceder 100 caracteres"));
            }

            if (strlen(trim($data->username)) > 50) {
                sendResponse(400, array("message" => "El username no puede exceder 50 caracteres"));
            }

            // Verificar si ya existe otro usuario con ese username
            if ($usuario->existeUsername(trim($data->username), $data->id_usuario)) {
                sendResponse(409, array("message" => "Ya existe otro usuario con ese username"));
            }

            // Validar email si se proporciona
            if (!empty($data->email) && !filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
                sendResponse(400, array("message" => "El email proporcionado no es válido"));
            }

            $usuario->id_usuario = intval($data->id_usuario);
            $usuario->nombre_completo = trim($data->nombre_completo);
            $usuario->email = !empty($data->email) ? trim($data->email) : null;
            $usuario->notificaciones_activas = isset($data->notificaciones_activas) ? intval($data->notificaciones_activas) : 1;
            $usuario->username = trim($data->username);
            $usuario->password = isset($data->password) ? trim($data->password) : '';
            $usuario->ID_ROL_USUARIO = intval($data->ID_ROL_USUARIO);
            $usuario->id_area = !empty($data->id_area) ? intval($data->id_area) : null;
            $usuario->estado = isset($data->estado) ? $data->estado : 'activo';

            if ($usuario->update()) {
                sendResponse(200, array(
                    "message" => "El usuario ha sido actualizado exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo actualizar el usuario. Verifique que existe."));
            }
        } catch (Exception $e) {
            error_log("Error en PUT usuarios: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al actualizar el usuario"));
        }
        break;

    case 'DELETE':
        try {
            $data = getJsonInput();

            // Validaciones
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            if (empty($data->id_usuario) || !is_numeric($data->id_usuario)) {
                sendResponse(400, array("message" => "ID de usuario inválido"));
            }

            $usuario->id_usuario = intval($data->id_usuario);

            if ($usuario->delete()) {
                sendResponse(200, array(
                    "message" => "El usuario ha sido eliminado exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo eliminar el usuario. Verifique que existe."));
            }
        } catch (Exception $e) {
            error_log("Error en DELETE usuarios: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al eliminar el usuario"));
        }
        break;

    default:
        sendResponse(405, array("message" => "Método no permitido"));
        break;
}
?>