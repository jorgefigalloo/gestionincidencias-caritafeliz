<?php
// api/controllers/incidencias.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 0);
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../models/database.php';
require_once '../models/Incidencia.php';
require_once '../models/Usuario.php';
require_once '../models/TipoIncidencia.php';
require_once '../models/SubtipoIncidencia.php'; // NUEVO
require_once '../../includes/email_notifier.php';

function sendResponse($code, $data) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

function getJsonInput() {
    $input = file_get_contents("php://input");
    $data = json_decode($input);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendResponse(400, array("message" => "JSON invÃ¡lido: " . json_last_error_msg()));
    }
    
    return $data;
}

$request_method = $_SERVER["REQUEST_METHOD"];
$action = $_GET['action'] ?? '';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        sendResponse(500, array("message" => "Error de conexiÃ³n a la base de datos"));
    }
    
    $incidencia = new Incidencia($db);
    $usuario = new Usuario($db);
    $tipoIncidencia = new TipoIncidencia($db);
    $subtipoIncidencia = new SubtipoIncidencia($db); // NUEVO
    
} catch (Exception $e) {
    error_log("Error al inicializar conexiÃ³n: " . $e->getMessage());
    sendResponse(500, array("message" => "Error interno del servidor"));
}

switch ($request_method) {
    case 'GET':
        try {
            switch ($action) {
                case 'stats':
                    $id_usuario = isset($_GET['id_usuario']) ? intval($_GET['id_usuario']) : null;
                    
                    if ($id_usuario) {
                        $stats = $incidencia->getStatsByUser($id_usuario);
                    } else {
                        $stats = $incidencia->getStats();
                    }

                    if ($stats !== false) {
                        sendResponse(200, array("stats" => $stats));
                    } else {
                        sendResponse(500, array("message" => "Error al obtener estadÃ­sticas"));
                    }
                    break;
                    
                case 'tecnicos':
                    try {
                        $query = "SELECT u.id_usuario, u.nombre_completo, u.username 
                                 FROM usuarios u 
                                 INNER JOIN rol_usuario r ON u.ID_ROL_USUARIO = r.id_rol 
                                 WHERE r.nombre_rol = 'tecnico' AND u.estado = 'activo' 
                                 ORDER BY u.nombre_completo";
                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        
                        $tecnicos_arr = array();
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $tecnico_item = array(
                                "id_usuario" => intval($row['id_usuario']),
                                "nombre_completo" => $row['nombre_completo'],
                                "username" => $row['username']
                            );
                            array_push($tecnicos_arr, $tecnico_item);
                        }
                        sendResponse(200, array("tecnicos" => $tecnicos_arr));
                    } catch (PDOException $e) {
                        sendResponse(500, array("message" => "Error al obtener tÃ©cnicos"));
                    }
                    break;

                case 'by_user':
                    $id_usuario = isset($_GET['id_usuario']) ? intval($_GET['id_usuario']) : 0;
                    
                    if (!$id_usuario) {
                        sendResponse(400, array("message" => "ID de usuario requerido"));
                    }

                    $stmt = $incidencia->readByUser($id_usuario);
                    
                    if ($stmt === false) {
                        sendResponse(500, array("message" => "Error al consultar las incidencias"));
                    }
                    
                    $num = $stmt->rowCount();

                    if ($num > 0) {
                        $incidencias_arr = array();
                        $incidencias_arr["records"] = array();
                        $incidencias_arr["total"] = $num;

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $incidencia_item = array(
                                "id_incidencia" => intval($row['id_incidencia']),
                                "titulo" => html_entity_decode($row['titulo']),
                                "descripcion" => html_entity_decode($row['descripcion']),
                                "respuesta_solucion" => html_entity_decode($row['respuesta_solucion'] ?? ''),
                                "id_tipo_incidencia" => $row['id_tipo_incidencia'],
                                "tipo_nombre" => $row['tipo_nombre'],
                                "id_subtipo_incidencia" => $row['id_subtipo_incidencia'],
                                "subtipo_nombre" => $row['subtipo_nombre'] ?? null,
                                "estado" => $row['estado'],
                                "prioridad" => $row['prioridad'],
                                "fecha_reporte" => $row['fecha_reporte'],
                                "tecnico_asignado" => html_entity_decode($row['tecnico_asignado'] ?? '')
                            );
                            array_push($incidencias_arr["records"], $incidencia_item);
                        }

                        sendResponse(200, $incidencias_arr);
                    } else {
                        sendResponse(200, array(
                            "records" => array(),
                            "total" => 0,
                            "message" => "No se encontraron incidencias.",
                            "debug_num" => $num,
                            "debug_id" => $id_usuario
                        ));
                    }
                    break;
                    
                case 'tipos':
                    $stmt = $tipoIncidencia->read();
                    if ($stmt !== false) {
                        $tipos_arr = array();
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $tipo_item = array(
                                "id_tipo_incidencia" => intval($row['id_tipo_incidencia']),
                                "nombre" => $row['nombre']
                            );
                            array_push($tipos_arr, $tipo_item);
                        }
                        sendResponse(200, array("tipos" => $tipos_arr));
                    } else {
                        sendResponse(500, array("message" => "Error al obtener tipos de incidencia"));
                    }
                    break;
                    
                case 'single':
                    $id = $_GET['id'] ?? 0;
                    if (!$id || !is_numeric($id)) {
                        sendResponse(400, array("message" => "ID de incidencia invÃ¡lido"));
                    }
                    
                    $incidencia->id_incidencia = intval($id);
                    if ($incidencia->readOne()) {
                        $incidencia_item = array(
                            "id_incidencia" => $incidencia->id_incidencia,
                            "titulo" => $incidencia->titulo,
                            "descripcion" => $incidencia->descripcion,
                            "respuesta_solucion" => $incidencia->respuesta_solucion,
                            "id_tipo_incidencia" => $incidencia->id_tipo_incidencia,
                            "id_subtipo_incidencia" => $incidencia->id_subtipo_incidencia, // NUEVO
                            "id_usuario_reporta" => $incidencia->id_usuario_reporta,
                            "nombre_reporta" => $incidencia->nombre_reporta,
                            "email_reporta" => $incidencia->email_reporta,
                            "estado" => $incidencia->estado,
                            "prioridad" => $incidencia->prioridad,
                            "fecha_reporte" => $incidencia->fecha_reporte,
                            "fecha_cierre" => $incidencia->fecha_cierre,
                            "id_usuario_tecnico" => $incidencia->id_usuario_tecnico
                        );
                        sendResponse(200, array("incidencia" => $incidencia_item));
                    } else {
                        sendResponse(404, array("message" => "Incidencia no encontrada"));
                    }
                    break;
                    
                default:
                    $stmt = $incidencia->read();
                    
                    if ($stmt === false) {
                        sendResponse(500, array("message" => "Error al consultar las incidencias"));
                    }
                    
                    $num = $stmt->rowCount();

                    if ($num > 0) {
                        $incidencias_arr = array();
                        $incidencias_arr["records"] = array();
                        $incidencias_arr["total"] = $num;

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $incidencia_item = array(
                                "id_incidencia" => intval($row['id_incidencia']),
                                "titulo" => html_entity_decode($row['titulo']),
                                "descripcion" => html_entity_decode($row['descripcion']),
                                "respuesta_solucion" => html_entity_decode($row['respuesta_solucion'] ?? ''),
                                "id_tipo_incidencia" => $row['id_tipo_incidencia'],
                                "tipo_nombre" => $row['tipo_nombre'],
                                "id_subtipo_incidencia" => $row['id_subtipo_incidencia'], // NUEVO
                                "subtipo_nombre" => $row['subtipo_nombre'] ?? null, // NUEVO
                                "id_usuario_reporta" => $row['id_usuario_reporta'],
                                "nombre_reporta" => html_entity_decode($row['nombre_reporta'] ?? ''),
                                "email_reporta" => $row['email_reporta'],
                                "reporta_usuario" => html_entity_decode($row['reporta_usuario'] ?? ''),
                                "estado" => $row['estado'],
                                "prioridad" => $row['prioridad'],
                                "fecha_reporte" => $row['fecha_reporte'],
                                "fecha_cierre" => $row['fecha_cierre'],
                                "id_usuario_tecnico" => $row['id_usuario_tecnico'],
                                "tecnico_asignado" => html_entity_decode($row['tecnico_asignado'] ?? ''),
                                "nombre_area" => html_entity_decode($row['nombre_area'] ?? ''),
                                "nombre_sede" => html_entity_decode($row['nombre_sede'] ?? '')
                            );
                            array_push($incidencias_arr["records"], $incidencia_item);
                        }

                        sendResponse(200, $incidencias_arr);
                    } else {
                        sendResponse(200, array(
                            "records" => array(),
                            "total" => 0,
                            "message" => "No se encontraron incidencias."
                        ));
                    }
                    break;
            }
        } catch (Exception $e) {
            error_log("Error en GET incidencias: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al obtener las incidencias"));
        }
        break;

    case 'POST':
        try {

                                // Manejar acciÃ³n de confirmaciÃ³n de soluciÃ³n
                    if ($action === 'confirmar_solucion') {
                        $data = getJsonInput();
                        
                        if (empty($data->id_incidencia) || !is_numeric($data->id_incidencia)) {
                            sendResponse(400, array("message" => "ID de incidencia invÃ¡lido"));
                        }
                        
                        if (empty($data->confirmacion) || !in_array($data->confirmacion, ['solucionado', 'no_solucionado'])) {
                            sendResponse(400, array("message" => "ConfirmaciÃ³n invÃ¡lida"));
                        }
                        
                        try {
                            if (!class_exists('IncidenciaNotificaciones')) {
    require_once '../models/IncidenciaExtensions.php';
}
                            $incExt = new IncidenciaNotificaciones($db);
                            
                            $resultado = $incExt->confirmarSolucion(
                                intval($data->id_incidencia),
                                $data->confirmacion,
                                isset($data->comentario_usuario) ? trim($data->comentario_usuario) : null
                            );
                            
                            if ($resultado) {
                                sendResponse(200, array(
                                    "success" => true,
                                    "message" => "ConfirmaciÃ³n registrada exitosamente"
                                ));
                            } else {

            if (empty($data->titulo) || trim($data->titulo) === '') {
                sendResponse(400, array("message" => "El tÃ­tulo de la incidencia es requerido"));
            }

            if (empty($data->descripcion) || trim($data->descripcion) === '') {
                sendResponse(400, array("message" => "La descripciÃ³n de la incidencia es requerida"));
            }

            if (strlen(trim($data->titulo)) > 100) {
                sendResponse(400, array("message" => "El tÃ­tulo no puede exceder 100 caracteres"));
            }

            $estados_validos = array('abierta', 'en_proceso', 'cerrada', 'cancelada');
            if (isset($data->estado) && !in_array($data->estado, $estados_validos)) {
                sendResponse(400, array("message" => "Estado de incidencia invÃ¡lido"));
            }

            $prioridades_validas = array('baja', 'media', 'alta', 'critica');
            if (isset($data->prioridad) && !in_array($data->prioridad, $prioridades_validas)) {
                sendResponse(400, array("message" => "Prioridad de incidencia invÃ¡lida"));
            }

            if (isset($data->email_reporta) && !empty($data->email_reporta) && 
                !filter_var($data->email_reporta, FILTER_VALIDATE_EMAIL)) {
                sendResponse(400, array("message" => "Email invÃ¡lido"));
            }

            $incidencia->titulo = trim($data->titulo);
            $incidencia->descripcion = trim($data->descripcion);
            $incidencia->id_tipo_incidencia = isset($data->id_tipo_incidencia) ? intval($data->id_tipo_incidencia) : null;
            $incidencia->id_subtipo_incidencia = isset($data->id_subtipo_incidencia) ? intval($data->id_subtipo_incidencia) : null; // NUEVO
            $incidencia->id_usuario_reporta = isset($data->id_usuario_reporta) ? intval($data->id_usuario_reporta) : null;
            $incidencia->nombre_reporta = isset($data->nombre_reporta) ? trim($data->nombre_reporta) : '';
            $incidencia->email_reporta = isset($data->email_reporta) ? trim($data->email_reporta) : '';
            $incidencia->estado = isset($data->estado) ? $data->estado : 'abierta';
            $incidencia->prioridad = isset($data->prioridad) ? $data->prioridad : 'media';

            if ($incidencia->create()) {
                // Enviar notificaciÃ³n por email
                try {
                    // Obtener nombre del tipo de incidencia si existe
                    $tipoNombre = '';
                    if ($incidencia->id_tipo_incidencia) {
                        $tipoIncidencia->id_tipo_incidencia = $incidencia->id_tipo_incidencia;
                        if ($tipoIncidencia->readOne()) {
                            $tipoNombre = $tipoIncidencia->nombre;
                        }
                    }
                    
                    // NUEVO: Obtener nombre del subtipo si existe
                    $subtipoNombre = '';
                    if ($incidencia->id_subtipo_incidencia) {
                        $subtipoIncidencia->id_subtipo_incidencia = $incidencia->id_subtipo_incidencia;
                        if ($subtipoIncidencia->readOne()) {
                            $subtipoNombre = $subtipoIncidencia->nombre;
                        }
                    }
                    
                    $emailNotifier = new EmailNotifier();
                    $emailData = array(
                        'titulo' => $incidencia->titulo,
                        'descripcion' => $incidencia->descripcion,
                        'nombre_reporta' => $incidencia->nombre_reporta,
                        'email_reporta' => $incidencia->email_reporta,
                        'prioridad' => $incidencia->prioridad,
                        'tipo_nombre' => $tipoNombre,
                        'subtipo_nombre' => $subtipoNombre // NUEVO
                    );
                    
                    $emailEnviado = $emailNotifier->enviarNotificacionNuevaIncidencia($emailData);
                    
                    if ($emailEnviado) {
                        error_log("NotificaciÃ³n de email enviada exitosamente para nueva incidencia");
                    } else {
                        error_log("Advertencia: No se pudo enviar la notificaciÃ³n de email");
                    }
                    
                } catch (Exception $emailException) {
                    error_log("Error al enviar email de notificaciÃ³n: " . $emailException->getMessage());
                }
                
                // NUEVO: Notificar a administradores sobre nueva incidencia
                try {
                    if (!class_exists('AppEmailNotifier')) {
                        require_once '../models/EmailNotifier.php';
                    }
                    $notifier = new AppEmailNotifier($db);
                    
                    // Obtener todos los administradores activos
                    $stmtAdmins = $db->prepare("
                        SELECT u.id_usuario 
                        FROM usuarios u 
                        INNER JOIN rol_usuario r ON u.ID_ROL_USUARIO = r.id_rol 
                        WHERE r.nombre_rol = 'admin' AND u.estado = 'activo'
                    ");
                    $stmtAdmins->execute();
                    
                    // Obtener el ID de la incidencia reciÃ©n creada
                    $idIncidenciaCreada = $db->lastInsertId();
                    
                    // Notificar a cada administrador
                    while ($admin = $stmtAdmins->fetch(PDO::FETCH_ASSOC)) {
                        if ($incidencia->prioridad === 'critica') {
                            // NotificaciÃ³n especial para incidencias crÃ­ticas
                            $notifier->notificarIncidenciaCritica($idIncidenciaCreada, $admin['id_usuario']);
            }
        } catch (Exception $e) {
            error_log("Error en POST incidencias: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al crear la incidencia"));
        }
        break;

    case 'PUT':
        try {
            $data = getJsonInput();

            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            if (empty($data->id_incidencia) || !is_numeric($data->id_incidencia)) {
                sendResponse(400, array("message" => "ID de incidencia invÃ¡lido"));
            }

            if (isset($data->titulo) && strlen(trim($data->titulo)) > 100) {
                sendResponse(400, array("message" => "El tÃ­tulo no puede exceder 100 caracteres"));
            }

            $estados_validos = array('abierta', 'en_proceso', 'en_verificacion', 'cerrada', 'cancelada');
            if (isset($data->estado) && !in_array($data->estado, $estados_validos)) {
                sendResponse(400, array("message" => "Estado de incidencia invÃ¡lido"));
            }

            $prioridades_validas = array('baja', 'media', 'alta', 'critica');
            if (isset($data->prioridad) && !in_array($data->prioridad, $prioridades_validas)) {
                sendResponse(400, array("message" => "Prioridad de incidencia invÃ¡lida"));
            }

            if (isset($data->email_reporta) && !empty($data->email_reporta) && 
                !filter_var($data->email_reporta, FILTER_VALIDATE_EMAIL)) {
                sendResponse(400, array("message" => "Email invÃ¡lido"));
            }

            $incidencia->id_incidencia = intval($data->id_incidencia);
            
            // Obtener el estado y tÃ©cnico anterior antes de actualizar
            $incidencia->readOne();
            $estadoAnterior = $incidencia->estado;
            $tecnicoAnterior = $incidencia->id_usuario_tecnico;
            $incidencia->titulo = isset($data->titulo) ? trim($data->titulo) : '';
            $incidencia->descripcion = isset($data->descripcion) ? trim($data->descripcion) : '';
            $incidencia->respuesta_solucion = isset($data->respuesta_solucion) ? trim($data->respuesta_solucion) : '';
            $incidencia->id_tipo_incidencia = isset($data->id_tipo_incidencia) ? intval($data->id_tipo_incidencia) : null;
            $incidencia->id_subtipo_incidencia = isset($data->id_subtipo_incidencia) ? intval($data->id_subtipo_incidencia) : null; // NUEVO
            $incidencia->estado = isset($data->estado) ? $data->estado : '';
            $incidencia->prioridad = isset($data->prioridad) ? $data->prioridad : '';
            $incidencia->id_usuario_tecnico = isset($data->id_usuario_tecnico) ? intval($data->id_usuario_tecnico) : null;
            $incidencia->nombre_reporta = isset($data->nombre_reporta) ? trim($data->nombre_reporta) : '';
            $incidencia->email_reporta = isset($data->email_reporta) ? trim($data->email_reporta) : '';

            if ($incidencia->update()) {
                // Si el estado cambiÃ³, enviar notificaciÃ³n
                if ($estadoAnterior !== $incidencia->estado) {
                    try {
                        if (!class_exists('AppEmailNotifier')) {
                            require_once '../models/EmailNotifier.php';
                        }
                        $notifier = new AppEmailNotifier($db);
                        $notifier->notificarCambioEstado(
                            intval($data->id_incidencia),
                            $incidencia->estado,
                            $incidencia->respuesta_solucion
                        );
                    } catch (Exception $notifException) {
                        error_log("Error al enviar notificaciÃ³n de cambio de estado: " . $notifException->getMessage());
                        // No fallar la actualizaciÃ³n si la notificaciÃ³n falla
                    }
                    
                    // NUEVO: Enviar email automÃ¡tico al usuario
                    try {
                        require_once '../models/IncidenciaEmailNotifier.php';
                        $emailNotifier = new IncidenciaEmailNotifier($db);
                        $emailNotifier->enviarActualizacionEstado(
                            intval($data->id_incidencia),
                            $incidencia->estado,
                            $incidencia->respuesta_solucion
                        );
                    } catch (Exception $emailException) {
                        error_log("Error al enviar email de actualizaciÃ³n: " . $emailException->getMessage());
                        // No fallar la actualizaciÃ³n si el email falla
                    }
                }
                
                // Si cambiÃ³ el tÃ©cnico asignado, notificar al nuevo tÃ©cnico
                if ($tecnicoAnterior !== $incidencia->id_usuario_tecnico && $incidencia->id_usuario_tecnico) {
                    try {
                        if (!class_exists('AppEmailNotifier')) {
                            require_once '../models/EmailNotifier.php';
                        }
                        $notifier = new AppEmailNotifier($db);
                        $notifier->notificarAsignacion(
                            intval($data->id_incidencia),
                            intval($incidencia->id_usuario_tecnico)
                        );
                    } catch (Exception $notifException) {
                        error_log("Error al enviar notificaciÃ³n de asignaciÃ³n: " . $notifException->getMessage());
                        // No fallar la actualizaciÃ³n si la notificaciÃ³n falla
                    }
                }
                
                sendResponse(200, array(
                    "message" => "La incidencia ha sido actualizada exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo actualizar la incidencia. Verifique que existe."));
            }
        } catch (Exception $e) {
            error_log("Error en PUT incidencias: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al actualizar la incidencia"));
        }
        break;

    case 'DELETE':
        try {
            $data = getJsonInput();

            if (empty($data)) {
                sendResponse(400, array("message" => "No se recibieron datos"));
            }

            if (empty($data->id_incidencia) || !is_numeric($data->id_incidencia)) {
                sendResponse(400, array("message" => "ID de incidencia invÃ¡lido"));
            }

            $incidencia->id_incidencia = intval($data->id_incidencia);

            if ($incidencia->delete()) {
                sendResponse(200, array(
                    "message" => "La incidencia ha sido eliminada exitosamente.",
                    "success" => true
                ));
            } else {
                sendResponse(500, array("message" => "No se pudo eliminar la incidencia. Verifique que existe."));
            }
        } catch (Exception $e) {
            error_log("Error en DELETE incidencias: " . $e->getMessage());
            sendResponse(500, array("message" => "Error al eliminar la incidencia"));
        }
        break;

    default:
        sendResponse(405, array("message" => "MÃ©todo no permitido"));
        break;
}
?>