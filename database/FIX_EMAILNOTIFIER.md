# 🔧 CORRECCIÓN RÁPIDA - Error EmailNotifier

El error es: **"Cannot declare class EmailNotifier, because the name is already in use"**

## Solución:

En `api/controllers/incidencias.php`, línea ~275, **CAMBIA:**

```php
require_once '../models/IncidenciaExtensions.php';
```

**POR:**

```php
if (!class_exists('IncidenciaNotificaciones')) {
    require_once '../models/IncidenciaExtensions.php';
}
```

---

## Ubicación Exacta:

Busca esta sección (alrededor de la línea 275):

```php
try {
    require_once '../models/IncidenciaExtensions.php';  // <-- CAMBIAR ESTA LÍNEA
    $incExt = new IncidenciaNotificaciones($db);
```

Reemplaza con:

```php
try {
    if (!class_exists('IncidenciaNotificaciones')) {
        require_once '../models/IncidenciaExtensions.php';
    }
    $incExt = new IncidenciaNotificaciones($db);
```

---

Guarda y prueba de nuevo. Debería funcionar correctamente ahora.
