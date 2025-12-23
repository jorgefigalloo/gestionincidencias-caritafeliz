<?php
/**
 * Sistema de Notificaciones por Email para Actualizaciones de Incidencias
 * 
 * Este archivo maneja el envío automático de emails a los usuarios
 * cuando el técnico actualiza el estado de su incidencia.
 */

class IncidenciaEmailNotifier {
    private $pdo;
    private $smtpEnabled = false;
    
    public function __construct($dbConnection) {
        $this->pdo = $dbConnection;
        // Verificar si SMTP está configurado
        $this->smtpEnabled = $this->checkSMTPConfig();
    }
    
    /**
     * Verificar si SMTP está configurado
     */
    private function checkSMTPConfig() {
        // Aquí puedes verificar si tienes configuración SMTP
        // Por ahora, usaremos la función mail() de PHP
        return true;
    }
    
    /**
     * Enviar email de confirmación cuando se crea una nueva incidencia
     * 
     * @param int $idIncidencia ID de la incidencia recién creada
     * @return bool True si se envió correctamente
     */
    public function notificarCreacionIncidencia($idIncidencia) {
        try {
            // Obtener datos de la incidencia
            $stmt = $this->pdo->prepare("
                SELECT 
                    i.*,
                    COALESCE(u.email, i.email_reporta) as email_usuario,
                    COALESCE(u.nombre_completo, i.nombre_reporta) as nombre_usuario,
                    t.nombre as tipo_nombre
                FROM incidencias i
                LEFT JOIN usuarios u ON i.id_usuario_reporta = u.id_usuario
                LEFT JOIN tipos_incidencia t ON i.id_tipo_incidencia = t.id_tipo_incidencia
                WHERE i.id_incidencia = ?
            ");
            $stmt->execute([$idIncidencia]);
            $incidencia = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$incidencia || !$incidencia['email_usuario']) {
                error_log("No se pudo enviar email de creación: incidencia no encontrada o sin email");
                return false;
            }
            
            // Preparar el email
            $destinatario = $incidencia['email_usuario'];
            $asunto = "Incidencia Creada #{$incidencia['id_incidencia']} - {$incidencia['titulo']}";
            $mensaje = $this->generarMensajeCreacionHTML($incidencia);
            
            // Enviar el email
            $resultado = $this->enviarEmail($destinatario, $asunto, $mensaje);
            
            if ($resultado) {
                error_log("Email de confirmación de creación enviado exitosamente a: {$destinatario}");
            } else {
                error_log("Error al enviar email de confirmación a: {$destinatario}");
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Error en notificarCreacionIncidencia: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar email de actualización de estado al usuario
     * 
     * @param int $idIncidencia ID de la incidencia
     * @param string $nuevoEstado Nuevo estado de la incidencia
     * @param string $comentarioTecnico Comentario del técnico
     * @return bool True si se envió correctamente
     */
    public function enviarActualizacionEstado($idIncidencia, $nuevoEstado, $comentarioTecnico = '') {
        try {
            // Obtener datos de la incidencia y del usuario
            $stmt = $this->pdo->prepare("
                SELECT 
                    i.*,
                    COALESCE(u.email, i.email_reporta) as email_usuario,
                    COALESCE(u.nombre_completo, i.nombre_reporta) as nombre_usuario,
                    t.nombre_completo as nombre_tecnico
                FROM incidencias i
                LEFT JOIN usuarios u ON i.id_usuario_reporta = u.id_usuario
                LEFT JOIN usuarios t ON i.id_usuario_tecnico = t.id_usuario
                WHERE i.id_incidencia = ?
            ");
            $stmt->execute([$idIncidencia]);
            $incidencia = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$incidencia || !$incidencia['email_usuario']) {
                error_log("No se pudo enviar email: incidencia no encontrada o sin email");
                return false;
            }
            
            // Preparar el email
            $destinatario = $incidencia['email_usuario'];
            $asunto = $this->generarAsunto($incidencia, $nuevoEstado);
            $mensaje = $this->generarMensajeHTML($incidencia, $nuevoEstado, $comentarioTecnico);
            
            // Enviar el email
            $resultado = $this->enviarEmail($destinatario, $asunto, $mensaje);
            
            if ($resultado) {
                error_log("Email de actualización enviado exitosamente a: {$destinatario}");
            } else {
                error_log("Error al enviar email de actualización a: {$destinatario}");
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Error en enviarActualizacionEstado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar email manual personalizado desde el dashboard del técnico
     * 
     * @param int $idIncidencia ID de la incidencia
     * @param string $asunto Asunto del email
     * @param string $mensajePersonalizado Mensaje personalizado del técnico
     * @return bool True si se envió correctamente
     */
    public function enviarNotificacionManual($idIncidencia, $asunto, $mensajePersonalizado) {
        try {
            // Obtener datos de la incidencia y del usuario
            $stmt = $this->pdo->prepare("
                SELECT 
                    i.*,
                    COALESCE(u.email, i.email_reporta) as email_usuario,
                    COALESCE(u.nombre_completo, i.nombre_reporta) as nombre_usuario,
                    t.nombre_completo as nombre_tecnico
                FROM incidencias i
                LEFT JOIN usuarios u ON i.id_usuario_reporta = u.id_usuario
                LEFT JOIN usuarios t ON i.id_usuario_tecnico = t.id_usuario
                WHERE i.id_incidencia = ?
            ");
            $stmt->execute([$idIncidencia]);
            $incidencia = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$incidencia || !$incidencia['email_usuario']) {
                error_log("No se pudo enviar email manual: incidencia no encontrada o sin email");
                return false;
            }
            
            // Preparar el email
            $destinatario = $incidencia['email_usuario'];
            $mensaje = $this->generarMensajeManualHTML($incidencia, $asunto, $mensajePersonalizado);
            
            // Enviar el email
            $resultado = $this->enviarEmail($destinatario, $asunto, $mensaje);
            
            if ($resultado) {
                error_log("Email manual enviado exitosamente a: {$destinatario}");
            } else {
                error_log("Error al enviar email manual a: {$destinatario}");
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Error en enviarNotificacionManual: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generar asunto del email
     */
    private function generarAsunto($incidencia, $nuevoEstado) {
        $estadoTexto = $this->formatearEstado($nuevoEstado);
        return "Actualización de tu Incidencia #{$incidencia['id_incidencia']} - {$estadoTexto}";
    }
    
    /**
     * Generar mensaje HTML para email de creación de incidencia
     */
    private function generarMensajeCreacionHTML($incidencia) {
        $colorPrioridad = $this->getColorPrioridad($incidencia['prioridad']);
        $prioridadTexto = strtoupper($incidencia['prioridad']);
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f5f5f5; margin: 0; padding: 20px;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #0D9488 0%, #14B8A6 100%); padding: 30px 20px; text-align: center;'>
                    <h1 style='color: white; margin: 0; font-size: 24px; font-weight: bold;'>
                        ✓ Incidencia Recibida
                    </h1>
                    <p style='color: rgba(255,255,255,0.9); margin: 10px 0 0 0; font-size: 14px;'>
                        Clínica Carita Feliz - Soporte Técnico
                    </p>
                </div>
                
                <!-- Content -->
                <div style='padding: 30px 20px;'>
                    <p style='font-size: 16px; margin: 0 0 20px 0;'>
                        Hola <strong>{$incidencia['nombre_usuario']}</strong>,
                    </p>
                    
                    <p style='font-size: 15px; color: #555; margin: 0 0 25px 0;'>
                        Hemos recibido tu reporte de incidencia. Nuestro equipo técnico la revisará y te contactará pronto.
                    </p>
                    
                    <!-- Success Message -->
                    <div style='background-color: #D1FAE5; border: 2px solid #10B981; padding: 20px; border-radius: 8px; margin: 0 0 25px 0;'>
                        <p style='margin: 0; font-weight: bold; color: #065F46; font-size: 15px;'>
                            ✓ Tu incidencia ha sido registrada exitosamente
                        </p>
                    </div>
                    
                    <!-- Incident Details Card -->
                    <div style='background-color: #F0FDFA; border-left: 4px solid #0D9488; padding: 20px; border-radius: 8px; margin: 0 0 25px 0;'>
                        <h3 style='margin: 0 0 15px 0; color: #0D9488; font-size: 16px;'>Detalles de tu Incidencia</h3>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; color: #0D9488; width: 140px;'>ID de Incidencia:</td>
                                <td style='padding: 8px 0; color: #333;'>#{$incidencia['id_incidencia']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; color: #0D9488;'>Título:</td>
                                <td style='padding: 8px 0; color: #333;'>{$incidencia['titulo']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; color: #0D9488;'>Descripción:</td>
                                <td style='padding: 8px 0; color: #333;'>{$incidencia['descripcion']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; color: #0D9488;'>Prioridad:</td>
                                <td style='padding: 8px 0;'>
                                    <span style='display: inline-block; background-color: {$colorPrioridad}; color: white; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: bold;'>
                                        {$prioridadTexto}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; color: #0D9488;'>Estado:</td>
                                <td style='padding: 8px 0;'>
                                    <span style='display: inline-block; background-color: #EF4444; color: white; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: bold;'>
                                        ABIERTA
                                    </span>
                                </td>
                            </tr>";
        
        if (!empty($incidencia['tipo_nombre'])) {
            $html .= "
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; color: #0D9488;'>Tipo:</td>
                                <td style='padding: 8px 0; color: #333;'>{$incidencia['tipo_nombre']}</td>
                            </tr>";
        }
        
        $html .= "
                        </table>
                    </div>
                    
                    <!-- Next Steps -->
                    <div style='background-color: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px; border-radius: 8px; margin: 0 0 25px 0;'>
                        <p style='margin: 0 0 8px 0; font-weight: bold; color: #92400E; font-size: 14px;'>
                            📋 Próximos Pasos:
                        </p>
                        <ul style='margin: 0; padding-left: 20px; color: #78350F; font-size: 14px;'>
                            <li>Un técnico revisará tu incidencia</li>
                            <li>Te notificaremos cuando sea asignada</li>
                            <li>Recibirás actualizaciones por email</li>
                        </ul>
                    </div>
                    
                    <p style='font-size: 14px; color: #555; margin: 0 0 10px 0;'>
                        Puedes ver el estado de tu incidencia en cualquier momento ingresando al sistema.
                    </p>
                    
                    <p style='font-size: 14px; color: #555; margin: 0;'>
                        Saludos,<br>
                        <strong>Equipo de Soporte Técnico</strong><br>
                        Clínica Carita Feliz
                    </p>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #F3F4F6; padding: 20px; text-align: center; border-top: 1px solid #E5E7EB;'>
                    <p style='margin: 0; font-size: 12px; color: #6B7280;'>
                        Este es un mensaje automático del Sistema de Gestión de Incidencias
                    </p>
                    <p style='margin: 8px 0 0 0; font-size: 12px; color: #6B7280;'>
                        Por favor, no respondas a este correo
                    </p>
                </div>
                
            </div>
        </body>
        </html>";
        
        return $html;
    }
    
    /**
     * Generar mensaje HTML para email manual del técnico
     */
    private function generarMensajeManualHTML($incidencia, $asunto, $mensajePersonalizado) {
        $estadoTexto = $this->formatearEstado($incidencia['estado']);
        $colorEstado = $this->getColorEstado($incidencia['estado']);
        $nombreTecnico = $incidencia['nombre_tecnico'] ?: 'Equipo de Soporte';
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f5f5f5; margin: 0; padding: 20px;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #0D9488 0%, #14B8A6 100%); padding: 30px 20px; text-align: center;'>
                    <h1 style='color: white; margin: 0; font-size: 24px; font-weight: bold;'>
                        Mensaje de Soporte Técnico
                    </h1>
                    <p style='color: rgba(255,255,255,0.9); margin: 10px 0 0 0; font-size: 14px;'>
                        Clínica Carita Feliz
                    </p>
                </div>
                
                <!-- Content -->
                <div style='padding: 30px 20px;'>
                    <p style='font-size: 16px; margin: 0 0 20px 0;'>
                        Hola <strong>{$incidencia['nombre_usuario']}</strong>,
                    </p>
                    
                    <!-- Incident Reference -->
                    <div style='background-color: #F0FDFA; border-left: 4px solid #0D9488; padding: 15px; border-radius: 8px; margin: 0 0 25px 0;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 4px 0; font-weight: bold; color: #0D9488; width: 120px;'>Incidencia:</td>
                                <td style='padding: 4px 0; color: #333;'>#{$incidencia['id_incidencia']} - {$incidencia['titulo']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 4px 0; font-weight: bold; color: #0D9488;'>Estado Actual:</td>
                                <td style='padding: 4px 0;'>
                                    <span style='display: inline-block; background-color: {$colorEstado}; color: white; padding: 2px 10px; border-radius: 12px; font-size: 12px; font-weight: bold;'>
                                        {$estadoTexto}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Mensaje Personalizado -->
                    <div style='background-color: #FFFFFF; border: 2px solid #E5E7EB; padding: 20px; border-radius: 8px; margin: 0 0 25px 0;'>
                        <p style='margin: 0; color: #333; font-size: 15px; line-height: 1.8; white-space: pre-wrap;'>{$mensajePersonalizado}</p>
                    </div>
                    
                    <p style='font-size: 14px; color: #555; margin: 0 0 10px 0;'>
                        Si tienes alguna pregunta o necesitas más información, no dudes en contactarnos.
                    </p>
                    
                    <p style='font-size: 14px; color: #555; margin: 0;'>
                        Atentamente,<br>
                        <strong>{$nombreTecnico}</strong><br>
                        Soporte Técnico - Clínica Carita Feliz
                    </p>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #F3F4F6; padding: 20px; text-align: center; border-top: 1px solid #E5E7EB;'>
                    <p style='margin: 0; font-size: 12px; color: #6B7280;'>
                        Sistema de Gestión de Incidencias - Clínica Carita Feliz
                    </p>
                </div>
                
            </div>
        </body>
        </html>";
        
        return $html;
    }
    
    /**
     * Generar mensaje HTML del email
     */
    private function generarMensajeHTML($incidencia, $nuevoEstado, $comentarioTecnico) {
        $estadoTexto = $this->formatearEstado($nuevoEstado);
        $colorEstado = $this->getColorEstado($nuevoEstado);
        $nombreTecnico = $incidencia['nombre_tecnico'] ?: 'Equipo de Soporte';
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f5f5f5; margin: 0; padding: 20px;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #0D9488 0%, #14B8A6 100%); padding: 30px 20px; text-align: center;'>
                    <h1 style='color: white; margin: 0; font-size: 24px; font-weight: bold;'>
                        Actualización de tu Incidencia
                    </h1>
                    <p style='color: rgba(255,255,255,0.9); margin: 10px 0 0 0; font-size: 14px;'>
                        Clínica Carita Feliz - Soporte Técnico
                    </p>
                </div>
                
                <!-- Content -->
                <div style='padding: 30px 20px;'>
                    <p style='font-size: 16px; margin: 0 0 20px 0;'>
                        Hola <strong>{$incidencia['nombre_usuario']}</strong>,
                    </p>
                    
                    <p style='font-size: 15px; color: #555; margin: 0 0 25px 0;'>
                        Te informamos que tu incidencia ha sido actualizada por nuestro equipo técnico:
                    </p>
                    
                    <!-- Incident Details Card -->
                    <div style='background-color: #F0FDFA; border-left: 4px solid #0D9488; padding: 20px; border-radius: 8px; margin: 0 0 25px 0;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; color: #0D9488; width: 140px;'>ID de Incidencia:</td>
                                <td style='padding: 8px 0; color: #333;'>#{$incidencia['id_incidencia']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; color: #0D9488;'>Título:</td>
                                <td style='padding: 8px 0; color: #333;'>{$incidencia['titulo']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; color: #0D9488;'>Nuevo Estado:</td>
                                <td style='padding: 8px 0;'>
                                    <span style='display: inline-block; background-color: {$colorEstado}; color: white; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: bold;'>
                                        {$estadoTexto}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; color: #0D9488;'>Prioridad:</td>
                                <td style='padding: 8px 0; color: #333;'>" . strtoupper($incidencia['prioridad']) . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; color: #0D9488;'>Atendido por:</td>
                                <td style='padding: 8px 0; color: #333;'>{$nombreTecnico}</td>
                            </tr>
                        </table>
                    </div>";
        
        // Agregar comentario del técnico si existe
        if (!empty($comentarioTecnico)) {
            $html .= "
                    <div style='background-color: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px; border-radius: 8px; margin: 0 0 25px 0;'>
                        <p style='margin: 0 0 8px 0; font-weight: bold; color: #92400E; font-size: 14px;'>
                            Comentario del Técnico:
                        </p>
                        <p style='margin: 0; color: #78350F; font-size: 14px; font-style: italic;'>
                            \"{$comentarioTecnico}\"
                        </p>
                    </div>";
        }
        
        // Mensaje específico según el estado
        if ($nuevoEstado === 'en_verificacion') {
            $html .= "
                    <div style='background-color: #DBEAFE; border: 2px solid #3B82F6; padding: 20px; border-radius: 8px; margin: 0 0 25px 0;'>
                        <p style='margin: 0 0 12px 0; font-weight: bold; color: #1E40AF; font-size: 15px;'>
                            Acción Requerida
                        </p>
                        <p style='margin: 0; color: #1E3A8A; font-size: 14px; line-height: 1.6;'>
                            Por favor, ingresa al sistema y confirma si el problema ha sido resuelto haciendo clic en el botón 
                            <strong>\"¿Está Solucionado?\"</strong> en tu panel de incidencias.
                        </p>
                    </div>";
        } elseif ($nuevoEstado === 'cerrada') {
            $html .= "
                    <div style='background-color: #D1FAE5; border: 2px solid #10B981; padding: 20px; border-radius: 8px; margin: 0 0 25px 0;'>
                        <p style='margin: 0 0 12px 0; font-weight: bold; color: #065F46; font-size: 15px;'>
                            Incidencia Cerrada
                        </p>
                        <p style='margin: 0; color: #047857; font-size: 14px; line-height: 1.6;'>
                            Tu incidencia ha sido resuelta y cerrada. Si el problema persiste o tienes alguna duda, 
                            no dudes en reportar una nueva incidencia.
                        </p>
                    </div>";
        }
        
        $html .= "
                    <p style='font-size: 14px; color: #555; margin: 0 0 10px 0;'>
                        Puedes ver más detalles ingresando a tu panel de incidencias en el sistema.
                    </p>
                    
                    <p style='font-size: 14px; color: #555; margin: 0;'>
                        Saludos,<br>
                        <strong>Equipo de Soporte Técnico</strong><br>
                        Clínica Carita Feliz
                    </p>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #F3F4F6; padding: 20px; text-align: center; border-top: 1px solid #E5E7EB;'>
                    <p style='margin: 0; font-size: 12px; color: #6B7280;'>
                        Este es un mensaje automático del Sistema de Gestión de Incidencias
                    </p>
                    <p style='margin: 8px 0 0 0; font-size: 12px; color: #6B7280;'>
                        Por favor, no respondas a este correo
                    </p>
                </div>
                
            </div>
        </body>
        </html>";
        
        return $html;
    }
    
    /**
     * Formatear estado para mostrar
     */
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
    
    /**
     * Obtener color según el estado
     */
    private function getColorEstado($estado) {
        $colores = [
            'abierta' => '#EF4444',      // Rojo
            'en_proceso' => '#F59E0B',   // Amarillo/Naranja
            'en_verificacion' => '#3B82F6', // Azul
            'cerrada' => '#10B981',      // Verde
            'cancelada' => '#6B7280'     // Gris
        ];
        return $colores[$estado] ?? '#6B7280';
    }
    
    /**
     * Obtener color según la prioridad
     */
    private function getColorPrioridad($prioridad) {
        $colores = [
            'baja' => '#3B82F6',      // Azul
            'media' => '#F59E0B',     // Amarillo/Naranja
            'alta' => '#F97316',      // Naranja
                        Si tienes alguna pregunta o necesitas más información, no dudes en contactarnos.
                    </p>
                    
                    <p style='font-size: 14px; color: #555; margin: 0;'>
                        Atentamente,<br>
