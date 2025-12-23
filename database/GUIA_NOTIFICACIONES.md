# 📚 Guía de Integración del Sistema de Notificaciones

## Archivos Creados

### Backend
1. **`api/models/EmailNotifier.php`** - Clase para envío de emails
2. **`api/controllers/notificaciones.php`** - API de notificaciones
3. **`api/models/IncidenciaExtensions.php`** - Métodos de workflow

### Database
4. **`database/migration_produccion.sql`** - Script SQL para producción (✅ EJECUTADO)
5. **`database/README_TRIGGERS.md`** - Documentación sobre triggers

---

## 🔧 Cómo Usar el Sistema

### 1. Configurar SMTP

Actualiza las credenciales en la tabla `configuracion_email`:

```sql
UPDATE configuracion_email 
SET 
    smtp_usuario = 'tu-email@gmail.com',
    smtp_password = 'tu-app-password-gmail',
    email_remitente = 'noreply@clinicacaritafeliz.com'
WHERE id_config = 1;
```

**Para Gmail:**
1. Activa la verificación en 2 pasos
2. Genera una "Contraseña de aplicación" en https://myaccount.google.com/apppasswords
3. Usa esa contraseña (no tu contraseña normal)

---

### 2. Integrar en el Código Existente

#### Opción A: Copiar métodos a Incidencia.php

Abre `api/models/IncidenciaExtensions.php` y copia los métodos al final de `Incidencia.php` (antes del `}`).

#### Opción B: Usar como clase separada

```php
require_once 'IncidenciaExtensions.php';

$incExt = new IncidenciaNotificaciones($db);
$incExt->asignarTecnico($idIncidencia, $idTecnico, $idUsuario);
```

---

### 3. Ejemplos de Uso

#### Asignar Técnico con Notificación

```php
require_once 'api/models/IncidenciaExtensions.php';

$incExt = new IncidenciaNotificaciones($db);
$resultado = $incExt->asignarTecnico(
    $idIncidencia,      // ID de la incidencia
    $idTecnico,         // ID del técnico a asignar
    $idUsuarioActual    // ID del usuario que hace el cambio (admin)
);
```

#### Cambiar Estado con Notificación

```php
$resultado = $incExt->cambiarEstado(
    $idIncidencia,
    'en_proceso',       // Nuevo estado
    $idUsuarioActual,
    'Estamos trabajando en tu caso'  // Comentario opcional
);
```

#### Usuario Confirma Solución

```php
$resultado = $incExt->confirmarSolucion(
    $idIncidencia,
    'solucionado',      // o 'no_solucionado'
    'Funciona perfectamente, gracias'  // Comentario opcional
);
```

---

## 📡 API de Notificaciones

### Endpoints Disponibles

#### 1. Listar Notificaciones
```
GET api/controllers/notificaciones.php?action=list&id_usuario=1
GET api/controllers/notificaciones.php?action=list&id_usuario=1&no_leidas=1
```

#### 2. Contador de No Leídas
```
GET api/controllers/notificaciones.php?action=unread_count&id_usuario=1
```

#### 3. Marcar como Leída
```
POST api/controllers/notificaciones.php?action=mark_read
Body: {"id_notificacion": 5}
```

#### 4. Marcar Todas como Leídas
```
POST api/controllers/notificaciones.php?action=mark_all_read
```

---

## 🔄 Flujo de Trabajo Completo

```
1. Usuario reporta incidencia
   └─> Estado: ABIERTA

2. Admin asigna técnico
   └─> Método: asignarTecnico()
   └─> Email a: Técnico ✉️
   └─> Historial: "Técnico asignado"

3. Técnico cambia a EN_PROCESO
   └─> Método: cambiarEstado()
   └─> Email a: Usuario reportante ✉️
   └─> Historial: "abierta → en_proceso"

4. Técnico marca como CERRADA
   └─> Método: cambiarEstado()
   └─> Email a: Usuario reportante ✉️
   └─> Estado: confirmacion_usuario = 'pendiente'

5. Usuario confirma
   a) ✅ Solucionado
      └─> Método: confirmarSolucion('solucionado')
      └─> Email a: Técnico ✉️
      └─> Admin puede cerrar definitivamente

   b) ❌ No Solucionado
      └─> Método: confirmarSolucion('no_solucionado')
      └─> Email a: Técnico ✉️
      └─> Estado vuelve a: EN_PROCESO
```

---

## 🎨 Plantillas de Email

Los emails se envían en formato HTML con diseño profesional:

- **Asignación:** Notifica al técnico con detalles de la incidencia
- **Cambio de Estado:** Informa al usuario sobre actualizaciones
- **Confirmación:** Notifica al técnico sobre la respuesta del usuario

Puedes personalizar las plantillas en `EmailNotifier.php` (métodos `generarMensaje*`).

---

## 🧪 Pruebas

### 1. Probar Envío de Email

```php
require_once 'api/models/database.php';
require_once 'api/models/EmailNotifier.php';

$database = new Database();
$db = $database->getConnection();

$notifier = new EmailNotifier($db);
$resultado = $notifier->notificarAsignacion(1, 2); // ID incidencia, ID técnico

echo $resultado ? "Email enviado" : "Error al enviar";
```

### 2. Verificar Historial

```php
$incExt = new IncidenciaNotificaciones($db);
$historial = $incExt->getHistorial(1); // ID incidencia
print_r($historial);
```

---

## ⚠️ Troubleshooting

### Email no se envía

1. **Verifica credenciales SMTP** en `configuracion_email`
2. **Revisa logs:** `error_log` en PHP
3. **Gmail bloqueado:** Usa "Contraseña de aplicación"
4. **Puerto bloqueado:** Prueba puerto 465 (SSL) en lugar de 587 (TLS)

### Historial no se registra

- El historial se registra **manualmente** en PHP (no hay trigger en producción)
- Asegúrate de usar los métodos de `IncidenciaExtensions.php`

---

## 📝 Próximos Pasos

1. ✅ Configurar SMTP
2. ✅ Integrar métodos en código existente
3. 🔄 Crear UI para confirmación de usuario
4. 🔄 Agregar botón "Notificar" en dashboard
5. 🔄 Implementar sistema de notificaciones en header
