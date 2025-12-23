<?php
// api/controllers/notificaciones.php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once '../models/database.php';

// Verificar autenticación (básica)
session_start();
if (!isset($_SESSION['user_id']) && !isset($_GET['public'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($action) {
        // ========================================
        // LISTAR NOTIFICACIONES DEL USUARIO
        // ========================================
        case 'list':
            if ($method !== 'GET') {
                throw new Exception('Método no permitido');
            }
            
            $idUsuario = $_GET['id_usuario'] ?? $_SESSION['user_id'];
            $soloNoLeidas = isset($_GET['no_leidas']) && $_GET['no_leidas'] === '1';
            
            $sql = "
                SELECT 
                    n.*,
                    i.titulo as incidencia_titulo,
                    i.estado as incidencia_estado
                FROM notificaciones n
                INNER JOIN incidencias i ON n.id_incidencia = i.id_incidencia
                WHERE n.id_usuario_destino = ?
            ";
            
            if ($soloNoLeidas) {
                $sql .= " AND n.leida = 0";
            }
            
            $sql .= " ORDER BY n.fecha_envio DESC LIMIT 50";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$idUsuario]);
            $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'notificaciones' => $notificaciones,
                'total' => count($notificaciones)
            ]);
            break;
        
        // ========================================
        // CONTADOR DE NO LEÍDAS
        // ========================================
        case 'unread_count':
            if ($method !== 'GET') {
                throw new Exception('Método no permitido');
            }
            
            $idUsuario = $_GET['id_usuario'] ?? $_SESSION['user_id'];
            
            $stmt = $db->prepare("
                SELECT COUNT(*) as count 
                FROM notificaciones 
                WHERE id_usuario_destino = ? AND leida = 0
            ");
            $stmt->execute([$idUsuario]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'count' => (int)$result['count']
            ]);
            break;
        
        // ========================================
        // MARCAR COMO LEÍDA
        // ========================================
        case 'mark_read':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            $idNotificacion = $data['id_notificacion'] ?? null;
            
            if (!$idNotificacion) {
                throw new Exception('ID de notificación requerido');
            }
            
            $stmt = $db->prepare("
                UPDATE notificaciones 
                SET leida = 1, fecha_lectura = NOW() 
                WHERE id_notificacion = ? AND id_usuario_destino = ?
            ");
            $stmt->execute([$idNotificacion, $_SESSION['user_id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Notificación marcada como leída'
            ]);
            break;
        
        // ========================================
        // MARCAR TODAS COMO LEÍDAS
        // ========================================
        case 'mark_all_read':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $idUsuario = $_SESSION['user_id'];
            
            $stmt = $db->prepare("
                UPDATE notificaciones 
                SET leida = 1, fecha_lectura = NOW() 
                WHERE id_usuario_destino = ? AND leida = 0
            ");
            $stmt->execute([$idUsuario]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Todas las notificaciones marcadas como leídas',
                'affected' => $stmt->rowCount()
            ]);
            break;
        
        // ========================================
        // ELIMINAR NOTIFICACIÓN
        // ========================================
        case 'delete':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            $idNotificacion = $data['id_notificacion'] ?? null;
            
            if (!$idNotificacion) {
                throw new Exception('ID de notificación requerido');
            }
            
            $stmt = $db->prepare("
                DELETE FROM notificaciones 
                WHERE id_notificacion = ? AND id_usuario_destino = ?
            ");
            $stmt->execute([$idNotificacion, $_SESSION['user_id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Notificación eliminada'
            ]);
            break;
        
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
