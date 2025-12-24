# Script PowerShell para arreglar el envío de correos
# Ejecuta este script desde la raíz del proyecto

$file = "api/controllers/incidencias.php"
$content = Get-Content $file -Raw

# Buscar y reemplazar el bloque problemático
$oldCode = @'
                // Notificar al usuario (si hay email)
                if (!empty($incidencia->email_reporta)) {
                    try {
                        if (!class_exists('AppEmailNotifier')) {
                            require_once '../models/EmailNotifier.php';
                        }
                        $notifier = new AppEmailNotifier($db);
                        $notifier->notificarNuevaIncidenciaUsuario(
                            $incidencia->id_incidencia,
                            $incidencia->titulo,
                            $incidencia->email_reporta,
                            $incidencia->nombre_reporta
                        );
                    } catch (Exception $e) {
                        error_log("Error al enviar notificación al usuario: " . $e->getMessage());
                    }
                }

                // Notificar a administradores
                try {
                    if (!isset($notifier)) {
                        if (!class_exists('AppEmailNotifier')) {
                            require_once '../models/EmailNotifier.php';
                        }
                        $notifier = new AppEmailNotifier($db);
                    }
                    $notifier->notificarNuevaIncidencia($incidencia->id_incidencia, $incidencia->titulo, $incidencia->prioridad);
                } catch (Exception $e) {
                    error_log("Error al notificar admin nueva incidencia: " . $e->getMessage());
                }
'@

$newCode = @'
                // Enviar notificación por email
                try {
                    require_once '../helpers/send_new_incident_email.php';
                    enviarEmailNuevaIncidencia(
                        $db,
                        $incidencia->id_incidencia,
                        $incidencia->titulo,
                        $incidencia->descripcion,
                        $incidencia->nombre_reporta,
                        $incidencia->email_reporta,
                        $incidencia->prioridad,
                        $incidencia->id_tipo_incidencia,
                        $incidencia->id_subtipo_incidencia
                    );
                } catch (Exception $e) {
                    error_log("Error al enviar email: " . $e->getMessage());
                }
'@

$content = $content -replace [regex]::Escape($oldCode), $newCode
Set-Content $file -Value $content -NoNewline

Write-Host "Archivo actualizado correctamente!" -ForegroundColor Green
Write-Host "Ahora puedes probar creando una nueva incidencia." -ForegroundColor Cyan
