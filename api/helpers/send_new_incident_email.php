<?php
// api/helpers/send_new_incident_email.php
// Helper para enviar correo de nueva incidencia

function enviarEmailNuevaIncidencia($db, $incidenciaId, $titulo, $descripcion, $nombreReporta, $emailReporta, $prioridad, $idTipo, $idSubtipo) {
    try {
        // Usar AppEmailNotifier que maneja tanto emails como notificaciones en BD
        if (!class_exists('AppEmailNotifier')) {
            require_once __DIR__ . '/../models/EmailNotifier.php';
        }
        
        $notifier = new AppEmailNotifier($db);
        $enviado = false;
        
        // 1. Notificar al usuario reportante (si tiene email)
        if (!empty($emailReporta)) {
            try {
                // Nota: AppEmailNotifier::notificarNuevaIncidenciaUsuario internamente busca la incidencia en BD
                // para obtener tipo y subtipo, así que no necesitamos pasarlos explícitamente si ya están guardados
                $notifier->notificarNuevaIncidenciaUsuario(
                    $incidenciaId,
                    $titulo,
                    $emailReporta,
                    $nombreReporta
                );
            } catch (Exception $e) {
                error_log("Error notificando usuario: " . $e->getMessage());
            }
        }
        
        // 2. Notificar a administradores (Email + Notificación en Dashboard)
        try {
            // Este método envía emails a todos los admins y registra la notificación en la tabla 'notificaciones'
            $resultadoAdmin = $notifier->notificarNuevaIncidencia(
                $incidenciaId, 
                $titulo, 
                $prioridad
            );
            
            if ($resultadoAdmin) {
                $enviado = true;
                error_log("Notificaciones de nueva incidencia enviadas correctamente para ID: " . $incidenciaId);
            }
        } catch (Exception $e) {
            error_log("Error notificando admins: " . $e->getMessage());
        }
        
        return $enviado;
    } catch (Exception $e) {
        error_log("Error general en enviarEmailNuevaIncidencia: " . $e->getMessage());
        return false;
    }
}
?>
