<?php
// Script para agregar notificaciones a administradores en incidencias.php

$file = 'c:/xampp/htdocs/gestion-incidencias/api/controllers/incidencias.php';
$content = file_get_contents($file);

// Buscar el bloque donde se envía el email
$search = "                } catch (Exception \$emailException) {\r\n                    error_log(\"Error al enviar email de notificación: \" . \$emailException->getMessage());\r\n                }\r\n                \r\n                sendResponse(201, array(\r\n                    \"message\" => \"La incidencia ha sido creada exitosamente.\",\r\n                    \"success\" => true\r\n                ));";

$replace = "                } catch (Exception \$emailException) {\r\n                    error_log(\"Error al enviar email de notificación: \" . \$emailException->getMessage());\r\n                }\r\n                \r\n                // NUEVO: Notificar a administradores sobre nueva incidencia\r\n                try {\r\n                    if (!class_exists('AppEmailNotifier')) {\r\n                        require_once '../models/EmailNotifier.php';\r\n                    }\r\n                    \$notifier = new AppEmailNotifier(\$db);\r\n                    \r\n                    // Obtener todos los administradores activos\r\n                    \$stmtAdmins = \$db->prepare(\"\r\n                        SELECT u.id_usuario \r\n                        FROM usuarios u \r\n                        INNER JOIN rol_usuario r ON u.ID_ROL_USUARIO = r.id_rol \r\n                        WHERE r.nombre_rol = 'admin' AND u.estado = 'activo'\r\n                    \");\r\n                    \$stmtAdmins->execute();\r\n                    \r\n                    // Obtener el ID de la incidencia recién creada\r\n                    \$idIncidenciaCreada = \$db->lastInsertId();\r\n                    \r\n                    // Notificar a cada administrador\r\n                    while (\$admin = \$stmtAdmins->fetch(PDO::FETCH_ASSOC)) {\r\n                        if (\$incidencia->prioridad === 'critica') {\r\n                            // Notificación especial para incidencias críticas\r\n                            \$notifier->notificarIncidenciaCritica(\$idIncidenciaCreada, \$admin['id_usuario']);\r\n                        } else {\r\n                            // Notificación normal para nuevas incidencias\r\n                            \$notifier->notificarNuevaIncidencia(\$idIncidenciaCreada, \$admin['id_usuario']);\r\n                        }\r\n                    }\r\n                } catch (Exception \$notifException) {\r\n                    error_log(\"Error al notificar a administradores: \" . \$notifException->getMessage());\r\n                    // No fallar la creación si falla la notificación\r\n                }\r\n                \r\n                sendResponse(201, array(\r\n                    \"message\" => \"La incidencia ha sido creada exitosamente.\",\r\n                    \"success\" => true\r\n                ));";

$newContent = str_replace($search, $replace, $content);

if ($newContent !== $content) {
    file_put_contents($file, $newContent);
    echo "✅ Notificaciones agregadas al endpoint POST\n";
    echo "Los administradores ahora recibirán notificaciones cuando:\n";
    echo "  - Se crea una nueva incidencia (normal)\n";
    echo "  - Se crea una incidencia CRÍTICA (urgente)\n";
} else {
    echo "❌ No se pudo realizar el reemplazo\n";
}
?>
