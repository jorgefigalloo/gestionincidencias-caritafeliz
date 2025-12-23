<?php
// api/controllers/subtipos_incidencias.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../models/database.php';
require_once '../models/SubtipoIncidencia.php';

function sendResponse($code, $data) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

function getJsonInput() {
    $input = file_get_contents("php://input");
    $data = json_decode($input);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendResponse(400, array("message" => "JSON inválido: " . json_last_error_msg()));
    }
    
    return $data;
}

$request_method = $_SERVER["REQUEST_METHOD"];
$action = $_GET['action'] ?? '';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        sendResponse(500, array("message" => "Error de conexión a la base de datos"));
    }
    
    $subtipo = new SubtipoIncidencia($db);
    
} catch (Exception $e) {
    error_log("Error al inicializar conexión: " . $e->getMessage());
    sendResponse(500, array("message" => "Error interno del servidor"));
}

switch ($request_method) {
    case 'GET':
        try {
            switch ($action) {
                case 'by_tipo':
                    // Obtener subtipos por tipo de incidencia
                    $id_tipo = $_GET['id_tipo'] ?? 0;
                    
                    if (!$id_tipo || !is_numeric($id_tipo)) {
                        sendResponse(400, array("message" => "ID de tipo inválido"));
                    }
                    
                    $stmt = $subtipo->readByTipo(intval($id_tipo));
                    
                    if ($stmt === false) {
                        sendResponse(500, array("message" => "Error al consultar subtipos"));
                    }
                    
                    $subtipos_arr = array();
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $subtipo_item = array(
                            "id_subtipo_incidencia" => intval($row['id_subtipo_incidencia']),
                            "nombre" => $row['nombre'],
                            "descripcion" => $row['descripcion'] ?? '',
                            "estado" => $row['estado']
                        );
                        array_push($subtipos_arr, $subtipo_item);
                    }
                    
                    sendResponse(200, array(
                        "subtipos" => $subtipos_arr,
                        "total" => count($subtipos_arr)
                    ));
                    break;
                    
                case 'single':
                    // Obtener un subtipo específico
                    $id = $_GET['id'] ?? 0;
                    
                    if (!$id || !is_numeric($id)) {
                        sendResponse(400, array("message" => "ID de subtipo inválido"));
                    }
                    
                    $subtipo->id_subtipo_incidencia = intval($id);
                    
                    if ($subtipo->readOne()) {
                        $subtipo_item = array(
                            "id_subtipo_incidencia" => $subtipo->id_subtipo_incidencia,
                            "nombre" => $subtipo->nombre,
                            "descripcion" => $subtipo->descripcion,
                            "id_tipo_incidencia" => $subtipo->id_tipo_incidencia,
                            "estado" => $subtipo->estado,
                            "fecha_creacion" => $subtipo->fecha_creacion
                        );
                        sendResponse(200, array("subtipo" => $subtipo_item));
                    } else {
                        sendResponse(404, array("message" => "Subtipo no encontrado"));
                    }
                    break;
                    
                case 'stats':
                    // Obtener estadísticas de uso
                    $stats = $subtipo->getUsageStats();
                    sendResponse(200, array("stats" => $stats));
                    break;
                    
                default:
                    // Leer todos los subtipos
                    $stmt = $subtipo->read();
                    
                    if ($stmt === false) {
                        sendResponse(500, array("message" => "Error al consultar subtipos"));
                    }
                    
                    $num = $stmt->rowCount();
                    $subtipos_arr = array();
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $subtipo_item = array(
                            "id_subtipo_incidencia" => intval($row['id_subtipo_incidencia']),
                            "nombre" => $row['nombre'],
                            "descripcion" => $row['descripcion'] ?? '',
                            "id_tipo_incidencia" => intval($row['id_tipo_incidencia']),
                            "tipo_nombre" => $row['tipo_nombre'],
                            "estado" => $row['estado'],
                            "fecha_creacion" => $row['fecha_creacion']
                        );
                        array_push($subtipos_arr, $subtipo_item);
                    }
                    
                    sendResponse(200, array(
                        "records" => $subtipos_arr,
                        "total" => $num
                    ));
                    break;
            }
        } catch (Exception $e) {
            error_log("Error en GET subtipos: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al obtener subtipos"));
        }
        break;

    case 'POST':
        try {
            $data = getJsonInput();
            
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }
            
            if (empty($data->nombre) || trim($data->nombre) === '') {
                sendResponse(400, array("message" => "El nombre del subtipo es requerido"));
            }
            
            if (empty($data->id_tipo_incidencia) || !is_numeric($data->id_tipo_incidencia)) {
                sendResponse(400, array("message" => "El tipo de incidencia es requerido"));
            }
            
            if (strlen(trim($data->nombre)) > 100) {
                sendResponse(400, array("message" => "El nombre no puede exceder 100 caracteres"));
            }
            
            $subtipo->nombre = trim($data->nombre);
            $subtipo->descripcion = isset($data->descripcion) ? trim($data->descripcion) : '';
            $subtipo->id_tipo_incidencia = intval($data->id_tipo_incidencia);
            $subtipo->estado = isset($data->estado) ? $data->estado : 'activo';
            
            if ($subtipo->create()) {
                sendResponse(201, array(
                    "message" => "Subtipo creado exitosamente",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo crear el subtipo"));
            }
        } catch (Exception $e) {
            error_log("Error en POST subtipos: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al crear subtipo"));
        }
        break;

    case 'PUT':
        try {
            $data = getJsonInput();
            
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }
            
            if (empty($data->id_subtipo_incidencia) || !is_numeric($data->id_subtipo_incidencia)) {
                sendResponse(400, array("message" => "ID de subtipo inválido"));
            }
            
            if (isset($data->nombre) && strlen(trim($data->nombre)) > 100) {
                sendResponse(400, array("message" => "El nombre no puede exceder 100 caracteres"));
            }
            
            $subtipo->id_subtipo_incidencia = intval($data->id_subtipo_incidencia);
            $subtipo->nombre = isset($data->nombre) ? trim($data->nombre) : '';
            $subtipo->descripcion = isset($data->descripcion) ? trim($data->descripcion) : '';
            $subtipo->id_tipo_incidencia = isset($data->id_tipo_incidencia) ? intval($data->id_tipo_incidencia) : 0;
            $subtipo->estado = isset($data->estado) ? $data->estado : 'activo';
            
            if ($subtipo->update()) {
                sendResponse(200, array(
                    "message" => "Subtipo actualizado exitosamente",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo actualizar el subtipo"));
            }
        } catch (Exception $e) {
            error_log("Error en PUT subtipos: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al actualizar subtipo"));
        }
        break;

    case 'DELETE':
        try {
            $data = getJsonInput();
            
            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }
            
            if (empty($data->id_subtipo_incidencia) || !is_numeric($data->id_subtipo_incidencia)) {
                sendResponse(400, array("message" => "ID de subtipo inválido"));
            }
            
            $subtipo->id_subtipo_incidencia = intval($data->id_subtipo_incidencia);
            
            // Verificar si está en uso
            if ($subtipo->isInUse()) {
                sendResponse(400, array(
                    "message" => "No se puede eliminar: el subtipo está siendo usado por incidencias"
                ));
            }
            
            if ($subtipo->delete()) {
                sendResponse(200, array(
                    "message" => "Subtipo eliminado exitosamente",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo eliminar el subtipo"));
            }
        } catch (Exception $e) {
            error_log("Error en DELETE subtipos: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al eliminar subtipo"));
        }
        break;

    default:
        sendResponse(405, array("message" => "Método no permitido"));
        break;
}
?>