-- ============================================
-- SCRIPT DE ROLLBACK (Por si necesitas revertir)
-- ============================================

USE gestion_ti_clarita;

-- Eliminar triggers
DROP TRIGGER IF EXISTS trg_historial_estado_update;

-- Eliminar vistas
DROP VIEW IF EXISTS v_notificaciones_pendientes;
DROP VIEW IF EXISTS v_incidencias_completas;

-- Eliminar tablas nuevas
DROP TABLE IF EXISTS configuracion_email;
DROP TABLE IF EXISTS historial_estados;
DROP TABLE IF EXISTS notificaciones;

-- Revertir cambios en incidencias
ALTER TABLE incidencias
DROP COLUMN IF EXISTS fecha_confirmacion,
DROP COLUMN IF EXISTS comentario_usuario,
DROP COLUMN IF EXISTS confirmacion_usuario;

-- Revertir cambios en usuarios
ALTER TABLE usuarios
DROP COLUMN IF EXISTS notificaciones_activas,
DROP COLUMN IF EXISTS email;

-- Verificación
SHOW TABLES;
SHOW COLUMNS FROM usuarios;
SHOW COLUMNS FROM incidencias;
