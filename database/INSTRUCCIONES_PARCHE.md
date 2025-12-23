# 🎯 INSTRUCCIONES PASO A PASO - Aplicar Parche

## ⚠️ IMPORTANTE: El botón NO aparece porque falta este cambio

---

## Paso 1: Abrir el Archivo

1. Abre tu editor de código (VS Code, Sublime, Notepad++, etc.)
2. Navega a: `c:\xampp\htdocs\gestion-incidencias\api\models\Incidencia.php`

---

## Paso 2: Buscar la Primera Ubicación (Línea ~38)

Presiona `Ctrl + F` y busca:
```
i.estado,
```

Encontrarás algo como esto:

```php
i.nombre_reporta,
i.email_reporta,
i.estado,
i.prioridad,
i.fecha_reporte,
```

---

## Paso 3: Agregar las 3 Líneas

**DESPUÉS de `i.estado,`** agrega estas 3 líneas:

```php
i.nombre_reporta,
i.email_reporta,
i.estado,
i.confirmacion_usuario,
i.comentario_usuario,
i.fecha_confirmacion,
i.prioridad,
i.fecha_reporte,
```

---

## Paso 4: Buscar la Segunda Ubicación (Línea ~362)

Presiona `Ctrl + F` de nuevo y busca la **SIGUIENTE** ocurrencia de:
```
i.estado,
```

Encontrarás algo similar:

```php
i.nombre_reporta,
i.email_reporta,
i.estado,
i.prioridad,
i.fecha_reporte,
```

---

## Paso 5: Agregar las 3 Líneas (Segunda Vez)

**DESPUÉS de `i.estado,`** agrega las mismas 3 líneas:

```php
i.nombre_reporta,
i.email_reporta,
i.estado,
i.confirmacion_usuario,
i.comentario_usuario,
i.fecha_confirmacion,
i.prioridad,
i.fecha_reporte,
```

---

## Paso 6: Guardar

1. Presiona `Ctrl + S` para guardar
2. Cierra el archivo

---

## Paso 7: Probar

1. Ve a tu navegador
2. Recarga el dashboard: `http://localhost/gestion-incidencias/views/dashboard_usuario.php`
3. Presiona `F5` o `Ctrl + F5` (recarga forzada)
4. Deberías ver el botón **"¿Está Solucionado?"** en la incidencia #16

---

## ✅ Verificación Rápida

Si quieres verificar que lo hiciste bien, busca en el archivo:

```
Ctrl + F → busca: "confirmacion_usuario"
```

Deberías encontrar **2 ocurrencias** (una en cada función).

---

## 🆘 Si Sigue Sin Funcionar

1. Abre la consola del navegador (F12)
2. Ve a la pestaña "Network"
3. Recarga la página
4. Busca la petición a `incidencias.php?action=by_user`
5. Haz clic en ella
6. Ve a la pestaña "Response"
7. Busca si aparece `"confirmacion_usuario"` en la respuesta

Si NO aparece, el cambio no se aplicó correctamente.
