<?php
// includes/email_notifier.php
require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailNotifier {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->configureSMTP();
    }
    
    private function configureSMTP() {
        try {
            // Configuración del servidor SMTP
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'caritafelizapp@gmail.com';
            $this->mail->Password = 'sazh jtug iuka mpyf';
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = 587;
            
            // Configuración general
            $this->mail->setFrom('noreply@clinicacaritafeliz.com', 'Sistema de Gestión TI - Clínica Carita Feliz');
            $this->mail->CharSet = 'UTF-8';
            $this->mail->isHTML(true);
            
        } catch (Exception $e) {
            error_log("Error configurando SMTP: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Envía notificación de nueva incidencia
     * @param array $incidenciaData Datos de la incidencia
     * @return bool True si se envió correctamente
     */
    public function enviarNotificacionNuevaIncidencia($incidenciaData) {
        try {
            // Limpiar destinatarios previos
            $this->mail->clearAddresses();
            $this->mail->clearCCs();
            
            // Destinatarios principales
            $this->mail->addAddress('sistemas@clinicacaritafeliz.com', 'Área de Sistemas');
            $this->mail->addAddress('helpdesk@clinicacaritafeliz.com', 'Help Desk');
            $this->mail->addAddress('caritafelizapp@gmail.com', 'Gmail de pruebas');
            
            // Copia al reportante si tiene email
            if (!empty($incidenciaData['email_reporta'])) {
                $this->mail->addCC($incidenciaData['email_reporta'], $incidenciaData['nombre_reporta']);
            }
            
            // Asunto con más información
            $prioridad = strtoupper($incidenciaData['prioridad']);
            $tipoInfo = !empty($incidenciaData['tipo_nombre']) ? ' - ' . $incidenciaData['tipo_nombre'] : '';
            $this->mail->Subject = "[NUEVA INCIDENCIA - {$prioridad}]{$tipoInfo}: {$incidenciaData['titulo']}";
            
            // Cuerpo del mensaje
            $this->mail->Body = $this->generarHTMLEmail($incidenciaData);
            $this->mail->AltBody = $this->generarTextoPlano($incidenciaData);
            
            // Enviar
            $resultado = $this->mail->send();
            
            if ($resultado) {
                error_log("Email de incidencia enviado exitosamente");
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Error al enviar email de incidencia: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Genera el cuerpo HTML del email
     */
    private function generarHTMLEmail($data) {
        $prioridadColor = $this->obtenerColorPrioridad($data['prioridad']);
        $prioridadTexto = $this->obtenerTextoPrioridad($data['prioridad']);
        
        $html = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .info-box { background: white; padding: 20px; margin: 15px 0; border-left: 4px solid #667eea; border-radius: 5px; }
                .label { font-weight: bold; color: #555; display: inline-block; min-width: 150px; }
                .value { color: #333; }
                .priority-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; color: white; font-weight: bold; background: ' . $prioridadColor . '; }
                .type-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; background: #3b82f6; color: white; font-size: 12px; margin-right: 5px; }
                .subtype-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; background: #8b5cf6; color: white; font-size: 12px; }
                .footer { text-align: center; color: #777; font-size: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; }
                .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                .highlight { background: #fef3c7; padding: 2px 6px; border-radius: 3px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🚨 Nueva Incidencia Reportada</h1>
                    <p>Sistema de Gestión de TI - Clínica Carita Feliz</p>
                </div>
                
                <div class="content">
                    <div class="info-box">
                        <p><span class="label">Título:</span><br>
                        <strong style="font-size: 18px;">' . htmlspecialchars($data['titulo']) . '</strong></p>
                    </div>
                    
                    <div class="info-box">
                        <p><span class="label">Descripción:</span><br>
                        ' . nl2br(htmlspecialchars($data['descripcion'])) . '</p>
                    </div>
                    
                    <div class="info-box">
                        <p><span class="label">Reportado por:</span> <span class="value">' . htmlspecialchars($data['nombre_reporta']) . '</span></p>
                        <p><span class="label">Email:</span> <span class="value">' . htmlspecialchars($data['email_reporta']) . '</span></p>
                        <p><span class="label">Fecha:</span> <span class="value">' . date('d/m/Y H:i:s') . '</span></p>
                    </div>
                    
                    <div class="info-box">
                        <p><span class="label">Prioridad:</span> <span class="priority-badge">' . $prioridadTexto . '</span></p>
                        <p><span class="label">Estado:</span> <span class="value">Abierta</span></p>';
        
        // Tipo y Subtipo en la misma línea con badges
        if (!empty($data['tipo_nombre']) || !empty($data['subtipo_nombre'])) {
            $html .= '<p><span class="label">Categoría:</span><br>';
            
            if (!empty($data['tipo_nombre'])) {
                $html .= '<span class="type-badge">📁 ' . htmlspecialchars($data['tipo_nombre']) . '</span>';
            }
            
            if (!empty($data['subtipo_nombre'])) {
                $html .= '<span class="subtype-badge">🔖 ' . htmlspecialchars($data['subtipo_nombre']) . '</span>';
            }
            
            $html .= '</p>';
        }
        
        $html .= '
                    </div>
                    
                    <div style="text-align: center;">
                        <a href="https://gestion-incidencias-caritafeliz.wuaze.com/" class="btn">📊 Ver en el Dashboard</a>
                    </div>
                    
                    <div style="background: #fef3c7; padding: 15px; border-radius: 5px; margin-top: 20px; border-left: 4px solid #f59e0b;">
                        <p style="margin: 0; font-size: 14px; color: #92400e;">
                            <strong>⚡ Acción requerida:</strong> Por favor, revisa esta incidencia lo antes posible y asigna un técnico si es necesario.
                        </p>
                    </div>
                </div>
                
                <div class="footer">
                    <p style="margin: 5px 0;">Este es un mensaje automático del Sistema de Gestión de TI</p>
                    <p style="margin: 5px 0;"><strong>Clínica Carita Feliz</strong> - Departamento de Tecnología</p>
                    <p style="margin: 5px 0; color: #999;">Por favor, no responda a este correo</p>
                    <p style="margin-top: 15px; font-size: 11px; color: #999;">
                        Si tienes problemas con el enlace, copia y pega esta URL en tu navegador:<br>
                        <span style="color: #667eea;">https://gestion-incidencias-caritafeliz.wuaze.com/</span>
                    </p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Genera el cuerpo de texto plano del email
     */
    private function generarTextoPlano($data) {
        $prioridadTexto = $this->obtenerTextoPrioridad($data['prioridad']);
        
        $texto = "╔══════════════════════════════════════════════════════╗\n";
        $texto .= "║        NUEVA INCIDENCIA REPORTADA                    ║\n";
        $texto .= "║   Sistema de Gestión de TI - Clínica Carita Feliz   ║\n";
        $texto .= "╚══════════════════════════════════════════════════════╝\n\n";
        
        $texto .= "TÍTULO: " . $data['titulo'] . "\n";
        $texto .= "══════════════════════════════════════════════════════\n\n";
        
        $texto .= "DESCRIPCIÓN:\n";
        $texto .= $data['descripcion'] . "\n\n";
        
        $texto .= "══════════════════════════════════════════════════════\n";
        $texto .= "INFORMACIÓN DEL REPORTE\n";
        $texto .= "══════════════════════════════════════════════════════\n";
        $texto .= "Reportado por: " . $data['nombre_reporta'] . "\n";
        $texto .= "Email: " . $data['email_reporta'] . "\n";
        $texto .= "Fecha: " . date('d/m/Y H:i:s') . "\n\n";
        
        $texto .= "══════════════════════════════════════════════════════\n";
        $texto .= "CLASIFICACIÓN\n";
        $texto .= "══════════════════════════════════════════════════════\n";
        $texto .= "Prioridad: " . $prioridadTexto . "\n";
        $texto .= "Estado: Abierta\n";
        
        if (!empty($data['tipo_nombre'])) {
            $texto .= "Tipo: " . $data['tipo_nombre'] . "\n";
        }
        
        // NUEVO: Incluir subtipo
        if (!empty($data['subtipo_nombre'])) {
            $texto .= "Subtipo: " . $data['subtipo_nombre'] . "\n";
        }
        
        $texto .= "\n══════════════════════════════════════════════════════\n";
        $texto .= "ACCIÓN REQUERIDA\n";
        $texto .= "══════════════════════════════════════════════════════\n";
        $texto .= "Por favor, revisa esta incidencia lo antes posible y\n";
        $texto .= "asigna un técnico si es necesario.\n\n";
        
        $texto .= "Accede al dashboard en:\n";
        $texto .= "https://gestion-incidencias-caritafeliz.wuaze.com/\n\n";
        
        $texto .= "══════════════════════════════════════════════════════\n";
        $texto .= "Este es un mensaje automático.\n";
        $texto .= "Por favor, no responda a este correo.\n";
        $texto .= "══════════════════════════════════════════════════════\n";
        
        return $texto;
    }
    
    /**
     * Obtiene el color según la prioridad
     */
    private function obtenerColorPrioridad($prioridad) {
        $colores = [
            'baja' => '#10b981',
            'media' => '#f59e0b',
            'alta' => '#f97316',
            'critica' => '#ef4444'
        ];
        return $colores[$prioridad] ?? '#6b7280';
    }
    
    /**
     * Obtiene el texto según la prioridad
     */
    private function obtenerTextoPrioridad($prioridad) {
        $textos = [
            'baja' => '🟢 BAJA',
            'media' => '🟡 MEDIA',
            'alta' => '🟠 ALTA',
            'critica' => '🔴 CRÍTICA'
        ];
        return $textos[$prioridad] ?? 'MEDIA';
    }
}
?>