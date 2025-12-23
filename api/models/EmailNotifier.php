<?php
// api/models/EmailNotifier.php

/**
 * Clase para gestionar el envío de notificaciones por email
 * Utiliza PHPMailer para envío SMTP
 */

// Importar PHPMailer (si usas Composer, descomenta estas líneas)
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

class AppEmailNotifier {
    private $pdo;
    private $config;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->cargarConfiguracion();
    }
    
    /**
     * Cargar configuración SMTP desde la base de datos
     */
    private function cargarConfiguracion() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM configuracion_email WHERE activo = 1 LIMIT 1");
            $stmt->execute();
            $this->config = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$this->config) {
                error_log("EmailNotifier: No hay configuración SMTP activa");
            }
        } catch (PDOException $e) {
            error_log("EmailNotifier: Error cargando config: " . $e->getMessage());
        }
    }
    
    /**
     * Enviar notificación cuando se asigna un técnico
     */
    public function notificarAsignacion($idIncidencia, $idTecnico) {
        try {
            // Obtener datos de la incidencia
            $stmt = $this->pdo->prepare("
                SELECT i.*, u.email, u.nombre_completo, u.notificaciones_activas
                FROM incidencias i
                INNER JOIN usuarios u ON u.id_usuario = ?
                WHERE i.id_incidencia = ?
            ");
            $stmt->execute([$idTecnico, $idIncidencia]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data || !$data['notificaciones_activas'] || !$data['email']) {
                return false; // Usuario sin email o notificaciones desactivadas
            }
            
            $asunto = "Nueva incidencia asignada: #{$idIncidencia} - {$data['titulo']}";
            $mensaje = $this->generarMensajeAsignacion($data);
            
            // Registrar notificación en BD
            $this->registrarNotificacion($idIncidencia, $idTecnico, 'asignacion', $asunto, $mensaje);
            
            // Enviar email
            return $this->enviarEmail($data['email'], $asunto, $mensaje);
            
        } catch (Exception $e) {
            error_log("EmailNotifier::notificarAsignacion: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar notificación cuando cambia el estado
     */
    public function notificarCambioEstado($idIncidencia, $nuevoEstado, $comentario = null) {
        try {
            // Obtener datos del usuario reportante
            $stmt = $this->pdo->prepare("
                SELECT i.*, 
                       COALESCE(u.email, i.email_reporta) as email,
                       COALESCE(u.nombre_completo, i.nombre_reporta) as nombre,
                       COALESCE(u.notificaciones_activas, 1) as notificaciones_activas
                FROM incidencias i
                LEFT JOIN usuarios u ON i.id_usuario_reporta = u.id_usuario
                WHERE i.id_incidencia = ?
            ");
            $stmt->execute([$idIncidencia]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data || !$data['email']) {
                return false;
            }
            
            $estadoTexto = $this->formatearEstado($nuevoEstado);
            $asunto = "Actualización de incidencia #{$idIncidencia}: {$estadoTexto}";
            $mensaje = $this->generarMensajeCambioEstado($data, $nuevoEstado, $comentario);
            
            // Registrar notificación
            $idUsuario = $data['id_usuario_reporta'] ?? null;
            if ($idUsuario) {
                $this->registrarNotificacion($idIncidencia, $idUsuario, 'cambio_estado', $asunto, $mensaje);
            }
            
            // Enviar email
            return $this->enviarEmail($data['email'], $asunto, $mensaje);
            
        } catch (Exception $e) {
            error_log("EmailNotifier::notificarCambioEstado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar notificación cuando el usuario confirma la solución
     */
    public function notificarConfirmacion($idIncidencia, $confirmacion, $comentarioUsuario = null) {
        try {
            // Obtener técnico asignado
            $stmt = $this->pdo->prepare("
                SELECT i.*, u.email, u.nombre_completo, u.notificaciones_activas
                FROM incidencias i
                INNER JOIN usuarios u ON i.id_usuario_tecnico = u.id_usuario
                WHERE i.id_incidencia = ?
            ");
            $stmt->execute([$idIncidencia]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data || !$data['notificaciones_activas'] || !$data['email']) {
                return false;
            }
            
            $confirmacionTexto = $confirmacion === 'solucionado' ? '✅ SOLUCIONADO' : '❌ NO SOLUCIONADO';
            $asunto = "Confirmación de usuario - Incidencia #{$idIncidencia}: {$confirmacionTexto}";
            $mensaje = $this->generarMensajeConfirmacion($data, $confirmacion, $comentarioUsuario);
            
            // Registrar notificación
            $this->registrarNotificacion($idIncidencia, $data['id_usuario_tecnico'], 'confirmacion', $asunto, $mensaje);
            
            // Enviar email
            return $this->enviarEmail($data['email'], $asunto, $mensaje);
            
        } catch (Exception $e) {
            error_log("EmailNotifier::notificarConfirmacion: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registrar notificación en la base de datos
     */
    private function registrarNotificacion($idIncidencia, $idUsuario, $tipo, $asunto, $mensaje) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO notificaciones 
                (id_incidencia, id_usuario_destino, tipo_notificacion, asunto, mensaje, enviada_email)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            return $stmt->execute([$idIncidencia, $idUsuario, $tipo, $asunto, $mensaje]);
        } catch (PDOException $e) {
            error_log("EmailNotifier::registrarNotificacion: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar email usando función mail() nativa de PHP
     * NOTA: Para producción, se recomienda usar PHPMailer con SMTP
     */
    private function enviarEmail($destinatario, $asunto, $mensaje) {
        if (!$this->config) {
            error_log("EmailNotifier: No hay configuración SMTP");
            return false;
        }
        
        // OPCIÓN 1: Usar mail() nativo (simple pero limitado)
        // return $this->enviarEmailNativo($destinatario, $asunto, $mensaje);
        
        // OPCIÓN 2: Usar PHPMailer con SMTP (recomendado)
        return $this->enviarEmailSMTP($destinatario, $asunto, $mensaje);
    }
    
    /**
     * Enviar email con función mail() nativa
     */
    private function enviarEmailNativo($destinatario, $asunto, $mensaje) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->config['nombre_remitente']} <{$this->config['email_remitente']}>\r\n";
        
        return mail($destinatario, $asunto, $mensaje, $headers);
    }
    
    /**
     * Enviar email con PHPMailer (SMTP)
     * REQUIERE: composer require phpmailer/phpmailer
     */
    private function enviarEmailSMTP($destinatario, $asunto, $mensaje) {
        // Si no tienes PHPMailer instalado, usa la versión nativa
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return $this->enviarEmailNativo($destinatario, $asunto, $mensaje);
        }
        
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_usuario'];
            $mail->Password = $this->config['smtp_password'];
            $mail->SMTPSecure = $this->config['smtp_seguridad'];
            $mail->Port = $this->config['smtp_port'];
            $mail->CharSet = 'UTF-8';
            
            // Remitente y destinatario
            $mail->setFrom($this->config['email_remitente'], $this->config['nombre_remitente']);
            $mail->addAddress($destinatario);
            
            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body = $mensaje;
            $mail->AltBody = strip_tags($mensaje);
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("EmailNotifier::enviarEmailSMTP: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    // ========================================
    // GENERADORES DE MENSAJES HTML
    // ========================================
    
    private function generarMensajeAsignacion($data) {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #0D9488; border-bottom: 2px solid #0D9488; padding-bottom: 10px;'>
                    Nueva Incidencia Asignada
                </h2>
                
                <p>Hola <strong>{$data['nombre_completo']}</strong>,</p>
                
                <p>Se te ha asignado una nueva incidencia:</p>
                
                <div style='background: #F0FDFA; padding: 15px; border-left: 4px solid #0D9488; margin: 20px 0;'>
                    <p><strong>ID:</strong> #{$data['id_incidencia']}</p>
                    <p><strong>Título:</strong> {$data['titulo']}</p>
                    <p><strong>Descripción:</strong> {$data['descripcion']}</p>
                    <p><strong>Prioridad:</strong> <span style='color: " . $this->getColorPrioridad($data['prioridad']) . ";'>" . strtoupper($data['prioridad']) . "</span></p>
                    <p><strong>Reportado por:</strong> {$data['nombre_reporta']}</p>
                </div>
                
                <p>Por favor, revisa la incidencia en el sistema y comienza a trabajar en ella.</p>
                
                <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px;'>
                    Este es un mensaje automático del Sistema de Gestión de Incidencias - Clínica Carita Feliz
                </p>
            </div>
        </body>
        </html>
        ";
    }
    
    private function generarMensajeCambioEstado($data, $nuevoEstado, $comentario) {
        $estadoTexto = $this->formatearEstado($nuevoEstado);
        $colorEstado = $this->getColorEstado($nuevoEstado);
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #0D9488; border-bottom: 2px solid #0D9488; padding-bottom: 10px;'>
                    Actualización de tu Incidencia
                </h2>
                
                <p>Hola <strong>{$data['nombre']}</strong>,</p>
                
                <p>Tu incidencia ha sido actualizada:</p>
                
                <div style='background: #F0FDFA; padding: 15px; border-left: 4px solid #0D9488; margin: 20px 0;'>
                    <p><strong>ID:</strong> #{$data['id_incidencia']}</p>
                    <p><strong>Título:</strong> {$data['titulo']}</p>
                    <p><strong>Nuevo Estado:</strong> <span style='color: {$colorEstado}; font-weight: bold;'>{$estadoTexto}</span></p>
                    " . ($comentario ? "<p><strong>Comentario del técnico:</strong><br>{$comentario}</p>" : "") . "
                </div>
                
                " . ($nuevoEstado === 'cerrada' ? "
                <div style='background: #D1FAE5; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='margin: 0;'><strong>&#x2705; Tu incidencia ha sido resuelta y cerrada.</strong></p>
                    <p style='margin: 10px 0 0 0;'>Si el problema persiste, por favor reporta una nueva incidencia.</p>
                </div>
                " : "") . "
                
                <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px;'>
                    Este es un mensaje automático del Sistema de Gestión de Incidencias - Clínica Carita Feliz
                </p>
            </div>
        </body>
        </html>
        ";
    }
    
    private function generarMensajeConfirmacion($data, $confirmacion, $comentarioUsuario) {
        $icono = $confirmacion === 'solucionado' ? '[SOLUCIONADO]' : '[NO SOLUCIONADO]';
        $color = $confirmacion === 'solucionado' ? '#10B981' : '#EF4444';
        $texto = $confirmacion === 'solucionado' ? 'SOLUCIONADO' : 'NO SOLUCIONADO';
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #0D9488; border-bottom: 2px solid #0D9488; padding-bottom: 10px;'>
                    {$icono} Confirmación del Usuario
                </h2>
                
                <p>Hola <strong>{$data['nombre_completo']}</strong>,</p>
                
                <p>El usuario ha confirmado el estado de la incidencia:</p>
                
                <div style='background: #F0FDFA; padding: 15px; border-left: 4px solid #0D9488; margin: 20px 0;'>
                    <p><strong>ID:</strong> #{$data['id_incidencia']}</p>
                    <p><strong>Título:</strong> {$data['titulo']}</p>
                    <p><strong>Confirmación:</strong> <span style='color: {$color}; font-weight: bold;'>{$texto}</span></p>
                    " . ($comentarioUsuario ? "<p><strong>Comentario del usuario:</strong><br>{$comentarioUsuario}</p>" : "") . "
                </div>
                
                " . ($confirmacion === 'no_solucionado' ? "
                <div style='background: #FEE2E2; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='margin: 0;'><strong>ATENCIÓN: El usuario reporta que el problema NO está solucionado.</strong></p>
                    <p style='margin: 10px 0 0 0;'>Por favor, revisa la incidencia nuevamente.</p>
                </div>
                " : "
                <div style='background: #D1FAE5; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='margin: 0;'><strong>¡Excelente trabajo! El usuario confirma que el problema está resuelto.</strong></p>
                </div>
                ") . "
                
                <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px;'>
                    Este es un mensaje automático del Sistema de Gestión de Incidencias - Clínica Carita Feliz
                </p>
            </div>
        </body>
        </html>
        ";
    }
    
    // ========================================
    // HELPERS
    // ========================================
    
    private function formatearEstado($estado) {
        $estados = [
            'abierta' => 'ABIERTA',
            'en_proceso' => 'EN PROCESO',
            'en_verificacion' => 'EN VERIFICACIÓN',
            'cerrada' => 'CERRADA',
            'cancelada' => 'CANCELADA'
        ];
        return $estados[$estado] ?? strtoupper($estado);
    }
    
    private function getColorEstado($estado) {
        $colores = [
            'abierta' => '#EF4444',
            'en_proceso' => '#F59E0B',
            'en_verificacion' => '#3B82F6',
            'cerrada' => '#10B981',
            'cancelada' => '#6B7280'
        ];
        return $colores[$estado] ?? '#6B7280';
    }
    
    private function getColorPrioridad($prioridad) {
        $colores = [
            'baja' => '#3B82F6',
            'media' => '#F59E0B',
            'alta' => '#F97316',
            'critica' => '#EF4444'
        ];
        return $colores[$prioridad] ?? '#6B7280';
    }
    
    /**
     * Enviar notificación cuando se crea una nueva incidencia
     */
    public function notificarNuevaIncidencia($idIncidencia, $idAdmin) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT i.*, 
                       u.email as admin_email, 
                       u.nombre_completo as admin_nombre,
                       u.notificaciones_activas,
                       COALESCE(ur.nombre_completo, i.nombre_reporta) as reportante
                FROM incidencias i
                LEFT JOIN usuarios ur ON i.id_usuario_reporta = ur.id_usuario
                CROSS JOIN usuarios u
                WHERE i.id_incidencia = ? AND u.id_usuario = ?
            ");
            $stmt->execute([$idIncidencia, $idAdmin]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data || !$data['notificaciones_activas'] || !$data['admin_email']) {
                return false;
            }
            
            $asunto = "Nueva incidencia reportada #{$idIncidencia}: {$data['titulo']}";
            $mensaje = $this->generarMensajeNuevaIncidencia($data);
            
            $this->registrarNotificacion($idIncidencia, $idAdmin, 'nueva_incidencia', $asunto, $mensaje);
            return $this->enviarEmail($data['admin_email'], $asunto, $mensaje);
            
        } catch (Exception $e) {
            error_log("EmailNotifier::notificarNuevaIncidencia: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar notificación urgente cuando se crea una incidencia crítica
     */
    public function notificarIncidenciaCritica($idIncidencia, $idAdmin) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT i.*, 
                       u.email as admin_email, 
                       u.nombre_completo as admin_nombre,
                       u.notificaciones_activas,
                       COALESCE(ur.nombre_completo, i.nombre_reporta) as reportante
                FROM incidencias i
                LEFT JOIN usuarios ur ON i.id_usuario_reporta = ur.id_usuario
                CROSS JOIN usuarios u
                WHERE i.id_incidencia = ? AND u.id_usuario = ?
            ");
            $stmt->execute([$idIncidencia, $idAdmin]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data || !$data['notificaciones_activas'] || !$data['admin_email']) {
                return false;
            }
            
            $asunto = "URGENTE - Incidencia CRÍTICA #{$idIncidencia}: {$data['titulo']}";
            $mensaje = $this->generarMensajeIncidenciaCritica($data);
            
            $this->registrarNotificacion($idIncidencia, $idAdmin, 'incidencia_critica', $asunto, $mensaje);
            return $this->enviarEmail($data['admin_email'], $asunto, $mensaje);
            
        } catch (Exception $e) {
            error_log("EmailNotifier::notificarIncidenciaCritica: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generar mensaje HTML para nueva incidencia
     */
    private function generarMensajeNuevaIncidencia($data) {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #0D9488; border-bottom: 2px solid #0D9488; padding-bottom: 10px;'>
                    Nueva Incidencia Reportada
                </h2>
                
                <p>Hola <strong>{$data['admin_nombre']}</strong>,</p>
                
                <p>Se ha reportado una nueva incidencia en el sistema:</p>
                
                <div style='background: #F0FDFA; padding: 15px; border-left: 4px solid #0D9488; margin: 20px 0;'>
                    <p><strong>ID:</strong> #{$data['id_incidencia']}</p>
                    <p><strong>Título:</strong> {$data['titulo']}</p>
                    <p><strong>Descripción:</strong> {$data['descripcion']}</p>
                    <p><strong>Prioridad:</strong> <span style='color: " . $this->getColorPrioridad($data['prioridad']) . ";'>" . strtoupper($data['prioridad']) . "</span></p>
                    <p><strong>Reportado por:</strong> {$data['reportante']}</p>
                </div>
                
                <p>Por favor, revisa y asigna un técnico para atender esta incidencia.</p>
                
                <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px;'>
                    Este es un mensaje automático del Sistema de Gestión de Incidencias - Clínica Carita Feliz
                </p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Generar mensaje HTML para incidencia crítica
     */
    private function generarMensajeIncidenciaCritica($data) {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 2px solid #EF4444; border-radius: 10px;'>
                <h2 style='color: #EF4444; border-bottom: 2px solid #EF4444; padding-bottom: 10px;'>
                    URGENTE - Incidencia CRÍTICA
                </h2>
                
                <p>Hola <strong>{$data['admin_nombre']}</strong>,</p>
                
                <div style='background: #FEE2E2; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='margin: 0; color: #991B1B;'><strong>ATENCIÓN: Se ha reportado una incidencia de prioridad CRÍTICA que requiere atención inmediata.</strong></p>
                </div>
                
                <div style='background: #FEF3C7; padding: 15px; border-left: 4px solid #F59E0B; margin: 20px 0;'>
                    <p><strong>ID:</strong> #{$data['id_incidencia']}</p>
                    <p><strong>Título:</strong> {$data['titulo']}</p>
                    <p><strong>Descripción:</strong> {$data['descripcion']}</p>
                    <p><strong>Prioridad:</strong> <span style='color: #EF4444; font-weight: bold;'>CRÍTICA</span></p>
                    <p><strong>Reportado por:</strong> {$data['reportante']}</p>
                </div>
                
                <p style='font-weight: bold; color: #991B1B;'>Esta incidencia requiere atención inmediata. Por favor, asigna un técnico lo antes posible.</p>
                
                <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px;'>
                    Este es un mensaje automático del Sistema de Gestión de Incidencias - Clínica Carita Feliz
                </p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Notificar a administradores cuando se asigna un técnico
     */
    public function notificarAdminAsignacion($idIncidencia, $idTecnico) {
        try {
            // Obtener datos de la incidencia y técnico asignado
            $stmt = $this->pdo->prepare("
                SELECT i.*, 
                       t.nombre_completo as tecnico_nombre,
                       COALESCE(ur.nombre_completo, i.nombre_reporta) as reportante
                FROM incidencias i
                LEFT JOIN usuarios ur ON i.id_usuario_reporta = ur.id_usuario
                LEFT JOIN usuarios t ON t.id_usuario = ?
                WHERE i.id_incidencia = ?
            ");
            $stmt->execute([$idTecnico, $idIncidencia]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data) {
                return false;
            }
            
            // Obtener todos los administradores con notificaciones activas
            $stmt = $this->pdo->prepare("
                SELECT u.id_usuario, u.email, u.nombre_completo
                FROM usuarios u
                INNER JOIN rol_usuario r ON u.ID_ROL_USUARIO = r.id_rol
                WHERE r.nombre_rol = 'admin' 
                AND u.notificaciones_activas = 1 
                AND u.email IS NOT NULL
                AND u.estado = 'activo'
            ");
            $stmt->execute();
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($admins)) {
                return false;
            }
            
            $asunto = "Técnico asignado a incidencia #{$idIncidencia}: {$data['titulo']}";
            
            // Enviar notificación a cada admin
            foreach ($admins as $admin) {
                $data['admin_nombre'] = $admin['nombre_completo'];
                $mensaje = $this->generarMensajeAdminAsignacion($data);
                
                // Registrar notificación
                $this->registrarNotificacion($idIncidencia, $admin['id_usuario'], 'admin_asignacion', $asunto, $mensaje);
                
                // Enviar email
                $this->enviarEmail($admin['email'], $asunto, $mensaje);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("EmailNotifier::notificarAdminAsignacion: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Notificar a administradores cuando un técnico cambia el estado
     */
    public function notificarAdminCambioEstado($idIncidencia, $nuevoEstado, $idTecnico, $comentario = null) {
        try {
            // Obtener datos de la incidencia y técnico
            $stmt = $this->pdo->prepare("
                SELECT i.*, 
                       t.nombre_completo as tecnico_nombre,
                       COALESCE(ur.nombre_completo, i.nombre_reporta) as reportante
                FROM incidencias i
                LEFT JOIN usuarios ur ON i.id_usuario_reporta = ur.id_usuario
                LEFT JOIN usuarios t ON t.id_usuario = ?
                WHERE i.id_incidencia = ?
            ");
            $stmt->execute([$idTecnico, $idIncidencia]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data) {
                return false;
            }
            
            // Obtener todos los administradores con notificaciones activas
            $stmt = $this->pdo->prepare("
                SELECT u.id_usuario, u.email, u.nombre_completo
                FROM usuarios u
                INNER JOIN rol_usuario r ON u.ID_ROL_USUARIO = r.id_rol
                WHERE r.nombre_rol = 'admin' 
                AND u.notificaciones_activas = 1 
                AND u.email IS NOT NULL
                AND u.estado = 'activo'
            ");
            $stmt->execute();
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($admins)) {
                return false;
            }
            
            $estadoTexto = $this->formatearEstado($nuevoEstado);
            $asunto = "Cambio de estado en incidencia #{$idIncidencia}: {$estadoTexto}";
            
            // Enviar notificación a cada admin
            foreach ($admins as $admin) {
                $data['admin_nombre'] = $admin['nombre_completo'];
                $data['nuevo_estado'] = $nuevoEstado;
                $data['comentario'] = $comentario;
                $mensaje = $this->generarMensajeAdminCambioEstado($data);
                
                // Registrar notificación
                $this->registrarNotificacion($idIncidencia, $admin['id_usuario'], 'admin_cambio_estado', $asunto, $mensaje);
                
                // Enviar email
                $this->enviarEmail($admin['email'], $asunto, $mensaje);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("EmailNotifier::notificarAdminCambioEstado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generar mensaje HTML para notificación de asignación a admin
     */
    private function generarMensajeAdminAsignacion($data) {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #0D9488; border-bottom: 2px solid #0D9488; padding-bottom: 10px;'>
                    Técnico Asignado a Incidencia
                </h2>
                
                <p>Hola <strong>{$data['admin_nombre']}</strong>,</p>
                
                <p>Se ha asignado un técnico a una incidencia:</p>
                
                <div style='background: #F0FDFA; padding: 15px; border-left: 4px solid #0D9488; margin: 20px 0;'>
                    <p><strong>ID:</strong> #{$data['id_incidencia']}</p>
                    <p><strong>Título:</strong> {$data['titulo']}</p>
                    <p><strong>Descripción:</strong> {$data['descripcion']}</p>
                    <p><strong>Prioridad:</strong> <span style='color: " . $this->getColorPrioridad($data['prioridad']) . ";'>" . strtoupper($data['prioridad']) . "</span></p>
                    <p><strong>Reportado por:</strong> {$data['reportante']}</p>
                    <p><strong>Técnico asignado:</strong> <span style='color: #0D9488; font-weight: bold;'>{$data['tecnico_nombre']}</span></p>
                </div>
                
                <p>El técnico ha sido notificado y comenzará a trabajar en la incidencia.</p>
                
                <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px;'>
                    Este es un mensaje automático del Sistema de Gestión de Incidencias - Clínica Carita Feliz
                </p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Generar mensaje HTML para notificación de cambio de estado a admin
     */
    private function generarMensajeAdminCambioEstado($data) {
        $estadoTexto = $this->formatearEstado($data['nuevo_estado']);
        $colorEstado = $this->getColorEstado($data['nuevo_estado']);
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #0D9488; border-bottom: 2px solid #0D9488; padding-bottom: 10px;'>
                    Cambio de Estado en Incidencia
                </h2>
                
                <p>Hola <strong>{$data['admin_nombre']}</strong>,</p>
                
                <p>Un técnico ha actualizado el estado de una incidencia:</p>
                
                <div style='background: #F0FDFA; padding: 15px; border-left: 4px solid #0D9488; margin: 20px 0;'>
                    <p><strong>ID:</strong> #{$data['id_incidencia']}</p>
                    <p><strong>Título:</strong> {$data['titulo']}</p>
                    <p><strong>Nuevo Estado:</strong> <span style='color: {$colorEstado}; font-weight: bold;'>{$estadoTexto}</span></p>
                    <p><strong>Técnico:</strong> {$data['tecnico_nombre']}</p>
                    " . ($data['comentario'] ? "<p><strong>Comentario:</strong><br>{$data['comentario']}</p>" : "") . "
                </div>
                
                " . ($data['nuevo_estado'] === 'cerrada' ? "
                <div style='background: #D1FAE5; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p style='margin: 0;'><strong>✅ Esta incidencia ha sido cerrada.</strong></p>
                </div>
                " : "") . "
                
                <p style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px;'>
                    Este es un mensaje automático del Sistema de Gestión de Incidencias - Clínica Carita Feliz
                </p>
            </div>
        </body>
        </html>
        ";
    }
}
?>
