ALTER TABLE incidencias MODIFY COLUMN estado ENUM('abierta', 'en_proceso', 'en_verificacion', 'cerrada', 'cancelada') DEFAULT 'abierta';
