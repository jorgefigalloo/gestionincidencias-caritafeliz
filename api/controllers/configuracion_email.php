<?php
// api/controllers/configuracion_email.php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

require_once '../models/database.php';

// Verificar autenticación y permisos de admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Solo administradores.']);
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
        // OBTENER CONFIGURACIÓN ACTUAL
        // ========================================
        case 'get':
            if ($method !== 'GET') {
                throw new Exception('Método no permitido');
            }
            
            $stmt = $db->prepare("SELECT * FROM configuracion_email WHERE id_config = 1");
            $stmt->execute();
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$config) {
                throw new Exception('No hay configuración SMTP');
            }
            
            // No enviar la contraseña completa por seguridad
            $config['smtp_password'] = '********';
            
            echo json_encode([
                'success' => true,
                'config' => $config
            ]);
            break;
        
        // ========================================
        // ACTUALIZAR CONFIGURACIÓN
        // ========================================
        case 'update':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Validar campos requeridos
            $required = ['smtp_host', 'smtp_port', 'smtp_usuario', 'email_remitente', 'nombre_remitente'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("El campo $field es requerido");
                }
            }
            
            // Construir query
            $query = "UPDATE configuracion_email SET 
                smtp_host = ?,
                smtp_port = ?,
                smtp_usuario = ?,
                email_remitente = ?,
                nombre_remitente = ?,
                smtp_seguridad = ?,
                activo = ?";
            
            $params = [
                $data['smtp_host'],
                $data['smtp_port'],
                $data['smtp_usuario'],
                $data['email_remitente'],
                $data['nombre_remitente'],
                $data['smtp_seguridad'] ?? 'tls',
                $data['activo'] ?? 1
            ];
            
            // Solo actualizar contraseña si se proporciona una nueva
            if (!empty($data['smtp_password']) && $data['smtp_password'] !== '********') {
                $query .= ", smtp_password = ?";
                $params[] = $data['smtp_password'];
            }
            
            $query .= " WHERE id_config = 1";
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            
            echo json_encode([
                'success' => true,
                'message' => 'Configuración SMTP actualizada correctamente'
            ]);
            break;
        
        // ========================================
        // PROBAR CONFIGURACIÓN (enviar email de prueba)
        // ========================================
        case 'test':
            if ($method !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            $emailDestino = $data['email_destino'] ?? $_SESSION['email'] ?? null;
            
            if (!$emailDestino) {
                throw new Exception('Email de destino requerido');
            }
            
            require_once '../models/EmailNotifier.php';
            $notifier = new EmailNotifier($db);
            
            // Crear un email de prueba
            $asunto = "Prueba de Configuración SMTP - Clínica Carita Feliz";
            $mensaje = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2 style='color: #0D9488;'>✅ Configuración SMTP Exitosa</h2>
                <p>Este es un email de prueba del Sistema de Gestión de Incidencias.</p>
                <p>Si recibes este mensaje, significa que la configuración SMTP está funcionando correctamente.</p>
                <hr>
                <p style='color: #666; font-size: 12px;'>Enviado el " . date('d/m/Y H:i:s') . "</p>
            </body>
            </html>
            ";
            
            // Intentar enviar
            $resultado = $notifier->enviarEmail($emailDestino, $asunto, $mensaje);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => "Email de prueba enviado a $emailDestino"
                ]);
            } else {
                throw new Exception('Error al enviar email de prueba. Verifica los logs del servidor.');
            }
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
