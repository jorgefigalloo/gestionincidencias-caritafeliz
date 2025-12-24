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
    
    $incidencia = new Incidencia($db);
    $usuario = new Usuario($db);
    $tipoIncidencia = new TipoIncidencia($db);
    $subtipoIncidencia = new SubtipoIncidencia($db); // NUEVO
    
} catch (Exception $e) {
    error_log("Error al inicializar conexión: " . $e->getMessage());
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
                        sendResponse(500, array("message" => "Error al obtener estadísticas"));
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
                        sendResponse(500, array("message" => "Error al obtener técnicos"));
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
                        sendResponse(400, array("message" => "ID de incidencia inválido"));
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
                                "id_subtipo_incidencia" => $row['id_subtipo_incidencia'],
                                "subtipo_nombre" => $row['subtipo_nombre'] ?? null,
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

            // Manejar acción de confirmación de solución
            if ($action === 'confirmar_solucion') {
                $data = getJsonInput();
                
                if (empty($data->id_incidencia) || !is_numeric($data->id_incidencia)) {
                    sendResponse(400, array("message" => "ID de incidencia inválido"));
                }
                
                if (empty($data->confirmacion) || !in_array($data->confirmacion, ['solucionado', 'no_solucionado'])) {
                    sendResponse(400, array("message" => "Confirmación inválida"));
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
                            "message" => "Confirmación registrada exitosamente"
                        ));
                    } else {
                        sendResponse(500, array("message" => "Error al registrar la confirmación"));
                    }
                } catch (Exception $e) {
                    error_log("Error en confirmar solución: " . $e->getMessage());
                    sendResponse(500, array("message" => "Error al confirmar solución"));
                }
                break;
            }

            // Manejar acción de envío manual de email
            if ($action === 'enviar_email_manual') {
                $data = getJsonInput();
                
                if (empty($data->id_incidencia) || !is_numeric($data->id_incidencia)) {
                    sendResponse(400, array("message" => "ID de incidencia inválido"));
                }
                
                if (empty($data->asunto) || trim($data->asunto) === '') {
                    sendResponse(400, array("message" => "El asunto es requerido"));
                }
                
                if (empty($data->mensaje) || trim($data->mensaje) === '') {
                    sendResponse(400, array("message" => "El mensaje es requerido"));
                }
                
                try {
                    if (!class_exists('IncidenciaEmailNotifier')) {
                        require_once '../models/IncidenciaEmailNotifier.php';
                    }
                    $emailNotifier = new IncidenciaEmailNotifier($db);
                    
                    $enviado = $emailNotifier->enviarNotificacionManual(
                        intval($data->id_incidencia),
                        trim($data->asunto),
                        trim($data->mensaje)
                    );
                    
                    if ($enviado) {
                        sendResponse(200, array(
                            "success" => true,
                            "message" => "Email enviado exitosamente"
                        ));
                    } else {
                        sendResponse(500, array("message" => "No se pudo enviar el email. Verifique los logs."));
                    }
                } catch (Exception $e) {
                    error_log("Error en envío manual de email: " . $e->getMessage());
                    sendResponse(500, array("message" => "Error al enviar email"));
                }
                break;
            }

            $data = getJsonInput();

            if (empty($data->titulo) || trim($data->titulo) === '') {
                sendResponse(400, array("message" => "El título de la incidencia es requerido"));
            }

            if (empty($data->descripcion) || trim($data->descripcion) === '') {
                sendResponse(400, array("message" => "La descripción de la incidencia es requerida"));
            }

            if (strlen(trim($data->titulo)) > 100) {
                sendResponse(400, array("message" => "El título no puede exceder 100 caracteres"));
            }

            $estados_validos = array('abierta', 'en_proceso', 'cerrada', 'cancelada');
            if (isset($data->estado) && !in_array($data->estado, $estados_validos)) {
                sendResponse(400, array("message" => "Estado de incidencia inválido"));
            }

            $prioridades_validas = array('baja', 'media', 'alta', 'critica');
            if (isset($data->prioridad) && !in_array($data->prioridad, $prioridades_validas)) {
                sendResponse(400, array("message" => "Prioridad de incidencia inválida"));
            }

            if (isset($data->email_reporta) && !empty($data->email_reporta) && 
                !filter_var($data->email_reporta, FILTER_VALIDATE_EMAIL)) {
                sendResponse(400, array("message" => "Email inválido"));
            }

            // Asignar datos a la instancia
            $incidencia->titulo = isset($data->titulo) ? trim($data->titulo) : '';
            $incidencia->descripcion = isset($data->descripcion) ? trim($data->descripcion) : '';
            $incidencia->id_tipo_incidencia = isset($data->id_tipo_incidencia) ? intval($data->id_tipo_incidencia) : null;
            $incidencia->id_subtipo_incidencia = isset($data->id_subtipo_incidencia) ? intval($data->id_subtipo_incidencia) : null;
            $incidencia->id_usuario_reporta = isset($data->id_usuario_reporta) ? intval($data->id_usuario_reporta) : null;
            $incidencia->nombre_reporta = isset($data->nombre_reporta) ? trim($data->nombre_reporta) : '';
            $incidencia->email_reporta = isset($data->email_reporta) ? trim($data->email_reporta) : '';
            $incidencia->estado = isset($data->estado) ? $data->estado : 'abierta';
            $incidencia->prioridad = isset($data->prioridad) ? $data->prioridad : 'media';

            if ($incidencia->create()) {
                // Obtener el ID de la incidencia creada
                $incidencia->id_incidencia = $db->lastInsertId();

                // Enviar notificación por email
                try {
                    require_once '../helpers/send_new_incident_email.php';
                    enviarEmailNuevaIncidencia(
                        $db,
                        $incidencia->id_incidencia,
                        $incidencia->titulo,
                        $incidencia->descripcion,
                        $incidencia->nombre_reporta,
                        $incidencia->email_reporta,
                        $incidencia->prioridad,
                        $incidencia->id_tipo_incidencia,
                        $incidencia->id_subtipo_incidencia
                    );
                } catch (Exception $e) {
                    error_log("Error al enviar email: " . $e->getMessage());
                }

                sendResponse(201, array("success" => true, "message" => "Incidencia creada exitosamente", "id" => $incidencia->id_incidencia));
            } else {
                sendResponse(500, array("message" => "No se pudo crear la incidencia. Inténtelo nuevamente."));
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
                sendResponse(400, array("message" => "ID de incidencia inválido"));
            }

            if (isset($data->titulo) && strlen(trim($data->titulo)) > 100) {
                sendResponse(400, array("message" => "El título no puede exceder 100 caracteres"));
            }

            $estados_validos = array('abierta', 'en_proceso', 'en_verificacion', 'cerrada', 'cancelada');
            if (isset($data->estado) && !in_array($data->estado, $estados_validos)) {
                sendResponse(400, array("message" => "Estado de incidencia inválido"));
            }

            $prioridades_validas = array('baja', 'media', 'alta', 'critica');
            if (isset($data->prioridad) && !in_array($data->prioridad, $prioridades_validas)) {
                sendResponse(400, array("message" => "Prioridad de incidencia inválida"));
            }

            if (isset($data->email_reporta) && !empty($data->email_reporta) && 
                !filter_var($data->email_reporta, FILTER_VALIDATE_EMAIL)) {
                sendResponse(400, array("message" => "Email inválido"));
            }

            $incidencia->id_incidencia = intval($data->id_incidencia);
            
            // Obtener el estado y técnico anterior antes de actualizar
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
                // Si el estado cambió, enviar notificación
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
                        error_log("Error al enviar notificación de cambio de estado: " . $notifException->getMessage());
                    }
                    
                    // NUEVO: Notificar a administradores sobre el cambio de estado
                    try {
                        // Solo notificar si hay un técnico asignado (quien hizo el cambio)
                        $idTecnicoAccion = $incidencia->id_usuario_tecnico; 
                        
                        if ($idTecnicoAccion) {
                            if (!isset($notifier)) {
                                if (!class_exists('AppEmailNotifier')) {
                                    require_once '../models/EmailNotifier.php';
                                }
                                $notifier = new AppEmailNotifier($db);
                            }
                            $notifier->notificarAdminCambioEstado(
                                intval($data->id_incidencia),
                                $incidencia->estado,
                                intval($idTecnicoAccion),
                                $incidencia->respuesta_solucion
                            );
                        }
                    } catch (Exception $adminException) {
                        error_log("Error al notificar admin cambio estado: " . $adminException->getMessage());
                    }
                }
                
                // Si cambió el técnico asignado, notificar al nuevo técnico
                // Usamos comparación no estricta (!=) o cast a int para evitar falsos positivos por tipos (string vs int)
                $tecnicoAnteriorInt = $tecnicoAnterior !== null ? intval($tecnicoAnterior) : null;
                $tecnicoNuevoInt = $incidencia->id_usuario_tecnico !== null ? intval($incidencia->id_usuario_tecnico) : null;

                if ($tecnicoAnteriorInt !== $tecnicoNuevoInt && $tecnicoNuevoInt) {
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
                        error_log("Error al enviar notificación de asignación: " . $notifException->getMessage());
                    }
                    
                    // NUEVO: Notificar a administradores sobre la asignación
                    try {
                        if (!isset($notifier)) {
                            if (!class_exists('AppEmailNotifier')) {
                                require_once '../models/EmailNotifier.php';
                            }
                            $notifier = new AppEmailNotifier($db);
                        }
                        $notifier->notificarAdminAsignacion(
                            intval($data->id_incidencia),
                            intval($incidencia->id_usuario_tecnico)
                        );
                    } catch (Exception $adminException) {
                        error_log("Error al notificar admin asignación: " . $adminException->getMessage());
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
                sendResponse(400, array("message" => "ID de incidencia inválido"));
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
        sendResponse(405, array("message" => "Método no permitido"));
        break;
}
?>