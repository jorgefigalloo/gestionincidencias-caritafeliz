<?php
// api/helpers/send_new_incident_email.php
// Helper para enviar correo de nueva incidencia

function enviarEmailNuevaIncidencia($db, $incidenciaId, $titulo, $descripcion, $nombreReporta, $emailReporta, $prioridad, $idTipo, $idSubtipo) {
    try {
        // Preparar datos para el email
        $incidenciaData = array(
            'id_incidencia' => $incidenciaId,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'nombre_reporta' => $nombreReporta,
            'email_reporta' => $emailReporta,
            'prioridad' => $prioridad,
            'estado' => 'abierta'
        );
        
        // Obtener nombre del tipo si existe
        if ($idTipo) {
            try {
                $queryTipo = "SELECT nombre FROM tipo_incidencia WHERE id_tipo_incidencia = :id_tipo";
                $stmtTipo = $db->prepare($queryTipo);
                $stmtTipo->bindParam(':id_tipo', $idTipo);
                $stmtTipo->execute();
                $tipoRow = $stmtTipo->fetch(PDO::FETCH_ASSOC);
                if ($tipoRow) {
                    $incidenciaData['tipo_nombre'] = $tipoRow['nombre'];
                }
            } catch (Exception $e) {
                error_log("Error obteniendo tipo: " . $e->getMessage());
            }
        }
        
        // Obtener nombre del subtipo si existe
        if ($idSubtipo) {
            try {
                $querySubtipo = "SELECT nombre FROM subtipo_incidencia WHERE id_subtipo_incidencia = :id_subtipo";
                $stmtSubtipo = $db->prepare($querySubtipo);
                $stmtSubtipo->bindParam(':id_subtipo', $idSubtipo);
                $stmtSubtipo->execute();
                $subtipoRow = $stmtSubtipo->fetch(PDO::FETCH_ASSOC);
                if ($subtipoRow) {
                    $incidenciaData['subtipo_nombre'] = $subtipoRow['nombre'];
                }
            } catch (Exception $e) {
                error_log("Error obteniendo subtipo: " . $e->getMessage());
            }
        }

        // Enviar notificación por email usando EmailNotifier
        require_once '../../includes/email_notifier.php';
        $emailNotifier = new EmailNotifier();
        $resultado = $emailNotifier->enviarNotificacionNuevaIncidencia($incidenciaData);
        
        if ($resultado) {
            error_log("Email de nueva incidencia enviado correctamente para ID: " . $incidenciaId);
        } else {
            error_log("No se pudo enviar email de nueva incidencia para ID: " . $incidenciaId);
        }
        
        return $resultado;
    } catch (Exception $e) {
        error_log("Error al enviar email de notificación: " . $e->getMessage());
        return false;
    }
}
?>
