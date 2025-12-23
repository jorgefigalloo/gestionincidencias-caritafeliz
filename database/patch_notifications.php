<?php
// Script para agregar notificaciones al endpoint PUT de incidencias

$file = 'c:/xampp/htdocs/gestion-incidencias/api/controllers/incidencias.php';
$content = file_get_contents($file);

// Buscar el bloque que queremos reemplazar
$search = "            if (\$incidencia->update()) {
                sendResponse(200, array(
                    \"message\" => \"La incidencia ha sido actualizada exitosamente.\",
                    \"success\" => true
                ));
            } else {
                sendResponse(500, array(\"message\" => \"No se pudo actualizar la incidencia. Verifique que existe.\"));
            }";

$replace = "            if (\$incidencia->update()) {
                // Si el estado cambió, enviar notificación
                if (\$estadoAnterior !== \$incidencia->estado) {
                    try {
                        if (!class_exists('AppEmailNotifier')) {
                            require_once '../models/EmailNotifier.php';
                        }
                        \$notifier = new AppEmailNotifier(\$db);
                        \$notifier->notificarCambioEstado(
                            intval(\$data->id_incidencia),
                            \$incidencia->estado,
                            \$incidencia->respuesta_solucion
                        );
                    } catch (Exception \$notifException) {
                        error_log(\"Error al enviar notificación de cambio de estado: \" . \$notifException->getMessage());
                        // No fallar la actualización si la notificación falla
                    }
                }
                
                sendResponse(200, array(
                    \"message\" => \"La incidencia ha sido actualizada exitosamente.\",
                    \"success\" => true
                ));
            } else {
                sendResponse(500, array(\"message\" => \"No se pudo actualizar la incidencia. Verifique que existe.\"));
            }";

// Realizar el reemplazo
$newContent = str_replace($search, $replace, $content);

if ($newContent !== $content) {
    file_put_contents($file, $newContent);
    echo "✅ Archivo actualizado exitosamente\n";
    echo "Se agregó el sistema de notificaciones al endpoint PUT\n";
} else {
    echo "❌ No se encontró el patrón a reemplazar\n";
    echo "Buscando variaciones...\n";
    
    // Intentar con diferentes formatos de salto de línea
    $search_rn = str_replace("\n", "\r\n", $search);
    $replace_rn = str_replace("\n", "\r\n", $replace);
    
    $newContent = str_replace($search_rn, $replace_rn, $content);
    
    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        echo "✅ Archivo actualizado exitosamente (con \\r\\n)\n";
    } else {
        echo "❌ No se pudo realizar el reemplazo\n";
    }
}
?>
