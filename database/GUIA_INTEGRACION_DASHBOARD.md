# 🎯 Guía de Integración - Dashboard de Usuario

## Paso 1: Agregar Scripts al Dashboard

En `views/dashboard_usuario.php`, agrega estos scripts en el `<head>`:

```html
<!-- Componente de Confirmación de Usuario -->
<script src="../assets/js/confirmacion-usuario.js" defer></script>
<script src="../assets/js/dashboard-usuario-enhanced.js" defer></script>
```

## Paso 2: Modificar la Función de Carga

En el `<script>` principal de `dashboard_usuario.php`, reemplaza la función `renderIncidents` con:

```javascript
// Usar la función mejorada
function renderIncidents(incidents) {
    renderIncidentsEnhanced(incidents);
}
```

## Paso 3: Actualizar la API para Incluir Confirmación

Modifica la consulta en `api/controllers/incidencias.php` para incluir los campos de confirmación:

```php
// En el SELECT, agregar:
i.confirmacion_usuario,
i.comentario_usuario,
i.fecha_confirmacion
```

---

## 🎨 Dashboard de Admin/Técnico

Para el dashboard de admin y técnico (`views/incidencias.php`), agrega estas funcionalidades:

### 1. Botón "Notificar Usuario"

Agrega este botón en cada fila de la tabla de incidencias:

```html
<button onclick="notificarCambioEstado(${incidencia.id_incidencia})" 
    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs">
    <i class="fas fa-bell mr-1"></i>
    Notificar
</button>
```

### 2. Indicador de Confirmación

Agrega una columna para mostrar el estado de confirmación:

```html
<td class="px-4 py-3">
    ${incidencia.confirmacion_usuario === 'solucionado' ? 
        '<span class="text-green-600"><i class="fas fa-check-double"></i> Confirmado</span>' :
    incidencia.confirmacion_usuario === 'no_solucionado' ?
        '<span class="text-orange-600"><i class="fas fa-exclamation-triangle"></i> Reabierto</span>' :
        '<span class="text-gray-400"><i class="fas fa-clock"></i> Pendiente</span>'
    }
</td>
```

### 3. JavaScript para Notificaciones

```javascript
async function notificarCambioEstado(idIncidencia) {
    const comentario = prompt('Mensaje para el usuario (opcional):');
    
    try {
        const response = await fetch('../api/controllers/incidencias.php', {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                id_incidencia: idIncidencia,
                // ... otros campos
            })
        });
        
        if (response.ok) {
            alert('Usuario notificado exitosamente');
        }
    } catch (error) {
        alert('Error al enviar notificación');
    }
}
```

---

## 📧 Panel de Configuración SMTP

Para acceder al panel de configuración SMTP (solo admin):

1. Agrega un enlace en el menú de admin:

```html
<a href="configuracion_smtp.php" class="menu-item">
    <i class="fas fa-envelope-open-text"></i>
    Configuración Email
</a>
```

2. El panel está en: `views/configuracion_smtp.php`

---

## ✅ Checklist de Integración

- [ ] Agregar scripts en `dashboard_usuario.php`
- [ ] Modificar función `renderIncidents`
- [ ] Actualizar API para incluir campos de confirmación
- [ ] Agregar botón "Notificar" en dashboard admin
- [ ] Agregar columna de confirmación en tabla
- [ ] Agregar enlace a configuración SMTP
- [ ] Configurar credenciales SMTP
- [ ] Probar flujo completo

---

## 🧪 Prueba del Sistema

1. **Como Usuario:**
   - Reporta una incidencia
   - Espera a que el técnico la cierre
   - Verás el botón "¿Está Solucionado?"
   - Haz clic y confirma

2. **Como Técnico:**
   - Asigna una incidencia
   - Cambia el estado a "Cerrada"
   - El usuario recibirá un email
   - Verás la confirmación del usuario

3. **Como Admin:**
   - Configura SMTP en `configuracion_smtp.php`
   - Envía un email de prueba
   - Verifica que llegue correctamente
