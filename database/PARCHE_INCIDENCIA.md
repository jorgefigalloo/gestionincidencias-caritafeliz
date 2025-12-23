# 🔧 PARCHE MANUAL - Incidencia.php

## Instrucciones:
Abre el archivo `api/models/Incidencia.php` y realiza estos cambios:

---

## CAMBIO 1: Función read() (Línea ~38)

**BUSCA estas líneas (alrededor de la línea 38):**
```php
                    i.email_reporta,
                    i.estado,
                    i.prioridad,
```

**REEMPLAZA con:**
```php
                    i.email_reporta,
                    i.estado,
                    i.confirmacion_usuario,
                    i.comentario_usuario,
                    i.fecha_confirmacion,
                    i.prioridad,
```

---

## CAMBIO 2: Función readByUser() (Línea ~362)

**BUSCA estas líneas (alrededor de la línea 362):**
```php
                    i.email_reporta,
                    i.estado,
                    i.prioridad,
```

**REEMPLAZA con:**
```php
                    i.email_reporta,
                    i.estado,
                    i.confirmacion_usuario,
                    i.comentario_usuario,
                    i.fecha_confirmacion,
                    i.prioridad,
```

---

## ✅ Verificación

Después de hacer los cambios:

1. Guarda el archivo
2. Recarga tu dashboard: `http://localhost/gestion-incidencias/views/dashboard_usuario.php`
3. Deberías ver el botón "¿Está Solucionado?" en las incidencias cerradas

---

## 🧪 Prueba Rápida

Abre: `http://localhost/gestion-incidencias/views/test_confirmacion.php`

Haz clic en "Probar API" y verifica que diga:
✅ El campo "confirmacion_usuario" está presente en la API

---

## ⚠️ Si no funciona

Si después de hacer los cambios sigue sin aparecer el botón:

1. Abre la consola del navegador (F12)
2. Ve a la pestaña "Console"
3. Busca errores en rojo
4. Copia y pega los errores para ayudarte mejor
