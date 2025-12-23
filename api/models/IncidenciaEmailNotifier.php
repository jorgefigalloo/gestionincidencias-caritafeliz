<?php
// api/models/IncidenciaEmailNotifier.php
require_once __DIR__ . '/../../includes/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../../includes/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../includes/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class IncidenciaEmailNotifier {
    private $pdo;
    
    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
    }
    
    public function enviarNotificacionManual($idIncidencia, $asunto, $mensajePersonalizado) {
        try {
            // Obtener información de la incidencia y el email del usuario
            // IMPORTANTE: Priorizamos email_reporta sobre el email del usuario
            // porque email_reporta es más específico para esta incidencia
            $stmt = $this->pdo->prepare("
                SELECT i.*, 
                       COALESCE(i.email_reporta, u.email) as email_usuario,
                       COALESCE(i.nombre_reporta, u.nombre_completo) as nombre_usuario
                FROM incidencias i 
                LEFT JOIN usuarios u ON i.id_usuario_reporta = u.id_usuario 
                WHERE i.id_incidencia = ?
            ");
            $stmt->execute([$idIncidencia]);
            $incidencia = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Log detallado para debugging
            error_log("IncidenciaEmailNotifier DEBUG: Incidencia #$idIncidencia");
            error_log("IncidenciaEmailNotifier DEBUG: email_reporta = " . ($incidencia['email_reporta'] ?? 'NULL'));
            error_log("IncidenciaEmailNotifier DEBUG: id_usuario_reporta = " . ($incidencia['id_usuario_reporta'] ?? 'NULL'));
            error_log("IncidenciaEmailNotifier DEBUG: email_usuario (COALESCE) = " . ($incidencia['email_usuario'] ?? 'NULL'));
            
            if (!$incidencia) {
                error_log("IncidenciaEmailNotifier: No se encontró la incidencia #$idIncidencia");
                return false;
            }
            
            if (empty($incidencia['email_usuario'])) {
                error_log("IncidenciaEmailNotifier: No se encontró email para incidencia #$idIncidencia");
                return false;
            }
            
            // Configurar PHPMailer con las mismas credenciales que funcionan en email_notifier.php
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'caritafelizapp@gmail.com';
            $mail->Password = 'sazh jtug iuka mpyf';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            
            // Configurar remitente y destinatario
            $mail->setFrom('noreply@clinicacaritafeliz.com', 'Sistema de Gestión TI - Clínica Carita Feliz');
            $mail->addAddress($incidencia['email_usuario'], $incidencia['nombre_usuario']);
            
            // Configurar el contenido del email
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            
            // Crear un HTML más profesional para el email
            $htmlBody = '
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .message-box { background: white; padding: 20px; margin: 15px 0; border-left: 4px solid #667eea; border-radius: 5px; }
                    .footer { text-align: center; color: #777; font-size: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>📧 ' . htmlspecialchars($asunto) . '</h1>
                        <p>Sistema de Gestión de TI - Clínica Carita Feliz</p>
                    </div>
                    
                    <div class="content">
                        <div class="message-box">
                            <p>' . nl2br(htmlspecialchars($mensajePersonalizado)) . '</p>
                        </div>
                        
                        <div style="background: #e0e7ff; padding: 15px; border-radius: 5px; margin-top: 20px;">
                            <p style="margin: 0; font-size: 14px; color: #3730a3;">
                                <strong>📋 Incidencia #' . $idIncidencia . '</strong><br>
                                ' . htmlspecialchars($incidencia['titulo']) . '
                            </p>
                        </div>
                    </div>
                    
                    <div class="footer">
                        <p style="margin: 5px 0;">Este es un mensaje del Sistema de Gestión de TI</p>
                        <p style="margin: 5px 0;"><strong>Clínica Carita Feliz</strong> - Departamento de Tecnología</p>
                        <p style="margin: 5px 0; color: #999;">Por favor, no responda a este correo</p>
                    </div>
                </div>
            </body>
            </html>';
            
            $mail->Body = $htmlBody;
            $mail->AltBody = strip_tags($mensajePersonalizado) . "\n\nIncidencia #" . $idIncidencia . ": " . $incidencia['titulo'];
            
            // Enviar el email
            $mail->send();
            
            error_log("IncidenciaEmailNotifier: Email enviado exitosamente a {$incidencia['email_usuario']}");
            return true;
            
        } catch (Exception $e) {
            error_log("IncidenciaEmailNotifier: Error al enviar email - " . $e->getMessage());
            return false;
        }
    }
    
    public function notificarCreacionIncidencia($idIncidencia) {
        // Placeholder para futuras notificaciones automáticas
        return true;
    }
}
?>
