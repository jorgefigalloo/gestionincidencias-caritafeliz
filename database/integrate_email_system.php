<?php
// Script para integrar el sistema de emails automáticos en incidencias.php

$file = 'c:/xampp/htdocs/gestion-incidencias/api/controllers/incidencias.php';
$content = file_get_contents($file);

// Buscar donde se envía la notificación de cambio de estado y agregar el envío de email
$search = "                // Si el estado cambió, enviar notificación\r\n                if (\$estadoAnterior !== \$incidencia->estado) {\r\n                    try {\r\n                        if (!class_exists('AppEmailNotifier')) {\r\n                            require_once '../models/EmailNotifier.php';\r\n                        }\r\n                        \$notifier = new AppEmailNotifier(\$db);\r\n                        \$notifier->notificarCambioEstado(\r\n                            intval(\$data->id_incidencia),\r\n                            \$incidencia->estado,\r\n                            \$incidencia->respuesta_solucion\r\n                        );\r\n                    } catch (Exception \$notifException) {\r\n                        error_log(\"Error al enviar notificación de cambio de estado: \" . \$notifException->getMessage());\r\n                        // No fallar la actualización si la notificación falla\r\n                    }\r\n                }";

$replace = "                // Si el estado cambió, enviar notificación\r\n                if (\$estadoAnterior !== \$incidencia->estado) {\r\n                    try {\r\n                        if (!class_exists('AppEmailNotifier')) {\r\n                            require_once '../models/EmailNotifier.php';\r\n                        }\r\n                        \$notifier = new AppEmailNotifier(\$db);\r\n                        \$notifier->notificarCambioEstado(\r\n                            intval(\$data->id_incidencia),\r\n                            \$incidencia->estado,\r\n                            \$incidencia->respuesta_solucion\r\n                        );\r\n                    } catch (Exception \$notifException) {\r\n                        error_log(\"Error al enviar notificación de cambio de estado: \" . \$notifException->getMessage());\r\n                        // No fallar la actualización si la notificación falla\r\n                    }\r\n                    \r\n                    // NUEVO: Enviar email automático al usuario\r\n                    try {\r\n                        require_once '../models/IncidenciaEmailNotifier.php';\r\n                        \$emailNotifier = new IncidenciaEmailNotifier(\$db);\r\n                        \$emailNotifier->enviarActualizacionEstado(\r\n                            intval(\$data->id_incidencia),\r\n                            \$incidencia->estado,\r\n                            \$incidencia->respuesta_solucion\r\n                        );\r\n                    } catch (Exception \$emailException) {\r\n                        error_log(\"Error al enviar email de actualización: \" . \$emailException->getMessage());\r\n                        // No fallar la actualización si el email falla\r\n                    }\r\n                }";

$newContent = str_replace($search, $replace, $content);

if ($newContent !== $content) {
    file_put_contents($file, $newContent);
    echo "✅ Sistema de emails automáticos integrado exitosamente\n";
    echo "Ahora cuando el técnico actualice una incidencia:\n";
    echo "  1. Se crea la notificación en la campanita\n";
    echo "  2. Se envía un email automático al usuario\n";
    echo "  3. El email incluye todos los detalles de la actualización\n";
} else {
    echo "❌ No se pudo realizar el reemplazo\n";
}
?>
