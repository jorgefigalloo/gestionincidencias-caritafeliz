-- ============================================
-- MIGRACIÓN PARA PRODUCCIÓN (InfinityFree)
-- Sin TRIGGERS - Lógica manejada por PHP
-- Fecha: 2025-12-22
-- ============================================

-- Usar la base de datos
USE if0_40354071_gestion_ti_clarita;

-- ============================================
-- PASO 1: Modificar tabla USUARIOS
-- ============================================

ALTER TABLE usuarios 
ADD COLUMN email VARCHAR(100) NULL AFTER nombre_completo,
ADD COLUMN notificaciones_activas TINYINT(1) DEFAULT 1 COMMENT 'Si el usuario quiere recibir notificaciones' AFTER email;

-- Actualizar emails de usuarios existentes
UPDATE usuarios SET email = 'admin@clinicacaritafeliz.com' WHERE id_usuario = 1;
UPDATE usuarios SET email = 'tecnico@clinicacaritafeliz.com' WHERE id_usuario = 2;
UPDATE usuarios SET email = 'usuario@clinicacaritafeliz.com' WHERE id_usuario = 3;

-- ============================================
-- PASO 2: Modificar tabla INCIDENCIAS
-- ============================================

ALTER TABLE incidencias
ADD COLUMN confirmacion_usuario ENUM('pendiente', 'solucionado', 'no_solucionado') DEFAULT 'pendiente' 
    COMMENT 'Confirmación del usuario sobre la solución' AFTER estado,
ADD COLUMN comentario_usuario TEXT NULL 
    COMMENT 'Comentario del usuario al confirmar' AFTER confirmacion_usuario,
ADD COLUMN fecha_confirmacion DATETIME NULL 
    COMMENT 'Fecha en que el usuario confirmó' AFTER comentario_usuario;

-- ============================================
-- PASO 3: Crear tabla NOTIFICACIONES
-- ============================================

CREATE TABLE notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_incidencia INT NOT NULL COMMENT 'Incidencia relacionada',
    id_usuario_destino INT NOT NULL COMMENT 'Usuario que recibe la notificación',
    tipo_notificacion ENUM('asignacion', 'cambio_estado', 'confirmacion', 'cierre') NOT NULL,
    asunto VARCHAR(200) NOT NULL COMMENT 'Asunto del email/notificación',
    mensaje TEXT COMMENT 'Cuerpo del mensaje',
    leida TINYINT(1) DEFAULT 0 COMMENT '0=No leída, 1=Leída',
    enviada_email TINYINT(1) DEFAULT 0 COMMENT '0=No enviada, 1=Enviada',
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_lectura DATETIME NULL,
    
    FOREIGN KEY (id_incidencia) REFERENCES incidencias(id_incidencia) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_destino) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    
    INDEX idx_usuario_leida (id_usuario_destino, leida),
    INDEX idx_fecha_envio (fecha_envio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Registro de notificaciones enviadas a usuarios';

-- ============================================
-- PASO 4: Crear tabla HISTORIAL_ESTADOS
-- ============================================

CREATE TABLE historial_estados (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_incidencia INT NOT NULL COMMENT 'Incidencia afectada',
    estado_anterior VARCHAR(20) NULL COMMENT 'Estado previo',
    estado_nuevo VARCHAR(20) NOT NULL COMMENT 'Nuevo estado',
    id_usuario_cambio INT NOT NULL COMMENT 'Usuario que realizó el cambio',
    comentario TEXT NULL COMMENT 'Comentario opcional del técnico',
    fecha_cambio DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_incidencia) REFERENCES incidencias(id_incidencia) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_cambio) REFERENCES usuarios(id_usuario),
    
    INDEX idx_incidencia (id_incidencia),
    INDEX idx_fecha (fecha_cambio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Historial de cambios de estado de incidencias';

-- ============================================
-- PASO 5: Crear tabla CONFIGURACION_EMAIL
-- ============================================

CREATE TABLE configuracion_email (
    id_config INT AUTO_INCREMENT PRIMARY KEY,
    smtp_host VARCHAR(100) NOT NULL DEFAULT 'smtp.gmail.com',
    smtp_port INT NOT NULL DEFAULT 587,
    smtp_usuario VARCHAR(100) NOT NULL,
    smtp_password VARCHAR(255) NOT NULL,
    email_remitente VARCHAR(100) NOT NULL,
    nombre_remitente VARCHAR(100) NOT NULL DEFAULT 'Sistema de Incidencias',
    smtp_seguridad ENUM('tls', 'ssl', 'none') DEFAULT 'tls',
    activo TINYINT(1) DEFAULT 1,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Configuración del servidor SMTP para envío de emails';

-- Insertar configuración por defecto
INSERT INTO configuracion_email 
(smtp_host, smtp_port, smtp_usuario, smtp_password, email_remitente, nombre_remitente) 
VALUES 
('smtp.gmail.com', 587, 'tu-email@gmail.com', 'tu-app-password', 'noreply@clinicacaritafeliz.com', 'Clínica Carita Feliz - Soporte TI');

-- ============================================
-- PASO 6: Crear VISTAS útiles
-- ============================================

-- Vista: Incidencias con información completa
CREATE OR REPLACE VIEW v_incidencias_completas AS
SELECT 
    i.id_incidencia,
    i.titulo,
    i.descripcion,
    i.estado,
    i.confirmacion_usuario,
    i.prioridad,
    i.fecha_reporte,
    i.fecha_cierre,
    
    -- Información del reportante
    i.nombre_reporta,
    i.email_reporta,
    ur.nombre_completo AS reportante_completo,
    ur.email AS reportante_email_usuario,
    
    -- Información del técnico asignado
    ut.nombre_completo AS tecnico_asignado,
    ut.email AS tecnico_email,
    
    -- Tipo y subtipo
    ti.nombre AS tipo_nombre,
    st.nombre AS subtipo_nombre,
    
    -- Área del reportante
    a.nombre_area,
    s.nombre_sede
    
FROM incidencias i
LEFT JOIN usuarios ur ON i.id_usuario_reporta = ur.id_usuario
LEFT JOIN usuarios ut ON i.id_usuario_tecnico = ut.id_usuario
LEFT JOIN tipos_incidencia ti ON i.id_tipo_incidencia = ti.id_tipo_incidencia
LEFT JOIN subtipos_incidencias st ON i.id_subtipo_incidencia = st.id_subtipo_incidencia
LEFT JOIN areas a ON ur.id_area = a.id_area
LEFT JOIN sedes s ON a.id_sede = s.id_sede;

-- Vista: Notificaciones pendientes por usuario
CREATE OR REPLACE VIEW v_notificaciones_pendientes AS
SELECT 
    n.id_notificacion,
    n.id_usuario_destino,
    u.nombre_completo AS destinatario,
    u.email AS email_destinatario,
    n.tipo_notificacion,
    n.asunto,
    n.mensaje,
    n.fecha_envio,
    i.id_incidencia,
    i.titulo AS incidencia_titulo,
    i.estado AS incidencia_estado
FROM notificaciones n
INNER JOIN usuarios u ON n.id_usuario_destino = u.id_usuario
INNER JOIN incidencias i ON n.id_incidencia = i.id_incidencia
WHERE n.leida = 0
ORDER BY n.fecha_envio DESC;

-- ============================================
-- VERIFICACIÓN
-- ============================================

SHOW COLUMNS FROM usuarios;
SHOW COLUMNS FROM incidencias;
SHOW TABLES LIKE '%notif%';
SHOW TABLES LIKE '%historial%';

-- ============================================
-- NOTAS IMPORTANTES PARA PRODUCCIÓN
-- ============================================

/*
✅ MIGRACIÓN COMPLETADA

⚠️ IMPORTANTE - SIN TRIGGERS:
- InfinityFree NO permite crear triggers
- El historial de estados se registrará desde PHP
- La clase Incidencia.php se encargará de esto

📝 PRÓXIMOS PASOS:
1. Actualizar emails reales en tabla 'usuarios'
2. Configurar credenciales SMTP en 'configuracion_email'
3. Implementar EmailNotifier.php
4. Modificar Incidencia.php para registrar historial manualmente
*/
