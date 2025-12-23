<?php
// Script para agregar notificación de asignación de técnico en incidencias.php

$file = 'c:/xampp/htdocs/gestion-incidencias/api/controllers/incidencias.php';
$content = file_get_contents($file);

// Buscar donde se captura el estado anterior y agregar captura de técnico anterior
$search1 = "            \$incidencia->id_incidencia = intval(\$data->id_incidencia);\n            \n            // Obtener el estado anterior antes de actualizar\n            \$incidencia->readOne();\n            \$estadoAnterior = \$incidencia->estado;";

$replace1 = "            \$incidencia->id_incidencia = intval(\$data->id_incidencia);\n            \n            // Obtener el estado y técnico anterior antes de actualizar\n            \$incidencia->readOne();\n            \$estadoAnterior = \$incidencia->estado;\n            \$tecnicoAnterior = \$incidencia->id_usuario_tecnico;";

// Buscar donde se envía la notificación de cambio de estado y agregar notificación de asignación
$search2 = "                // Si el estado cambió, enviar notificación\r\n                if (\$estadoAnterior !== \$incidencia->estado) {\r\n                    try {\r\n                        if (!class_exists('AppEmailNotifier')) {\r\n                            require_once '../models/EmailNotifier.php';\r\n                        }\r\n                        \$notifier = new AppEmailNotifier(\$db);\r\n                        \$notifier->notificarCambioEstado(\r\n                            intval(\$data->id_incidencia),\r\n                            \$incidencia->estado,\r\n                            \$incidencia->respuesta_solucion\r\n                        );\r\n                    } catch (Exception \$notifException) {\r\n                        error_log(\"Error al enviar notificación de cambio de estado: \" . \$notifException->getMessage());\r\n                        // No fallar la actualización si la notificación falla\r\n                    }\r\n                }";

$replace2 = "                // Si el estado cambió, enviar notificación\r\n                if (\$estadoAnterior !== \$incidencia->estado) {\r\n                    try {\r\n                        if (!class_exists('AppEmailNotifier')) {\r\n                            require_once '../models/EmailNotifier.php';\r\n                        }\r\n                        \$notifier = new AppEmailNotifier(\$db);\r\n                        \$notifier->notificarCambioEstado(\r\n                            intval(\$data->id_incidencia),\r\n                            \$incidencia->estado,\r\n                            \$incidencia->respuesta_solucion\r\n                        );\r\n                    } catch (Exception \$notifException) {\r\n                        error_log(\"Error al enviar notificación de cambio de estado: \" . \$notifException->getMessage());\r\n                        // No fallar la actualización si la notificación falla\r\n                    }\r\n                }\r\n                \r\n                // Si cambió el técnico asignado, notificar al nuevo técnico\r\n                if (\$tecnicoAnterior !== \$incidencia->id_usuario_tecnico && \$incidencia->id_usuario_tecnico) {\r\n                    try {\r\n                        if (!class_exists('AppEmailNotifier')) {\r\n                            require_once '../models/EmailNotifier.php';\r\n                        }\r\n                        \$notifier = new AppEmailNotifier(\$db);\r\n                        \$notifier->notificarAsignacion(\r\n                            intval(\$data->id_incidencia),\r\n                            intval(\$incidencia->id_usuario_tecnico)\r\n                        );\r\n                    } catch (Exception \$notifException) {\r\n                        error_log(\"Error al enviar notificación de asignación: \" . \$notifException->getMessage());\r\n                        // No fallar la actualización si la notificación falla\r\n                    }\r\n                }";

$newContent = str_replace($search1, $replace1, $content);
$newContent = str_replace($search2, $replace2, $newContent);

if ($newContent !== $content) {
    file_put_contents($file, $newContent);
    echo "✅ Notificación de asignación agregada al endpoint PUT\n";
    echo "Los técnicos ahora recibirán notificaciones cuando:\n";
    echo "  - Se les asigna una nueva incidencia\n";
    echo "  - Se les reasigna una incidencia existente\n";
} else {
    echo "❌ No se pudo realizar el reemplazo\n";
}
?>
