# Instrucciones para arreglar el envío de correos al crear incidencias

## Problema
El código en `api/controllers/incidencias.php` está intentando usar una clase `AppEmailNotifier` que no existe, causando errores de PHP que se muestran como HTML en lugar de JSON.

## Solución
En el archivo `api/controllers/incidencias.php`, busca la línea 388 que dice:
```php
// Notificar al usuario (si hay email)
```

Reemplaza TODO el bloque desde la línea 388 hasta la línea 417 (que termina con el segundo `}` del try-catch de administradores) con este código:

```php
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
```

## Verificación
Después de hacer el cambio:
1. Guarda el archivo
2. Intenta crear una nueva incidencia desde http://localhost:8085/gestion-incidencias/index.php
3. Deberías recibir el correo de notificación
4. No deberías ver errores de "Unexpected token '<'"

## Archivo helper ya creado
El archivo `api/helpers/send_new_incident_email.php` ya está creado y funciona correctamente.
