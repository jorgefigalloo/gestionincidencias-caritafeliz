-- ============================================
-- VERIFICACIÓN SIMPLE - Ejecuta esto directamente
-- ============================================

USE gestion_ti_clarita;

-- Verificar si los campos ya existen
SELECT 
    id_incidencia,
    titulo,
    estado,
    confirmacion_usuario,
    comentario_usuario,
    fecha_confirmacion
FROM incidencias
WHERE estado = 'cerrada'
LIMIT 5;

-- Si el query anterior da error "Unknown column 'confirmacion_usuario'"
-- significa que los campos NO existen y debes ejecutar lo siguiente:

-- ============================================
-- AGREGAR CAMPOS (solo si no existen)
-- ============================================

ALTER TABLE incidencias
ADD COLUMN confirmacion_usuario ENUM('pendiente', 'solucionado', 'no_solucionado') DEFAULT 'pendiente' 
    COMMENT 'Confirmación del usuario sobre la solución' AFTER estado;

ALTER TABLE incidencias
ADD COLUMN comentario_usuario TEXT NULL 
    COMMENT 'Comentario del usuario al confirmar' AFTER confirmacion_usuario;

ALTER TABLE incidencias
ADD COLUMN fecha_confirmacion DATETIME NULL 
    COMMENT 'Fecha en que el usuario confirmó' AFTER comentario_usuario;

-- Verificar que se agregaron correctamente
SHOW COLUMNS FROM incidencias LIKE '%confirmacion%';
SHOW COLUMNS FROM incidencias LIKE '%comentario_usuario%';
SHOW COLUMNS FROM incidencias LIKE '%fecha_confirmacion%';
