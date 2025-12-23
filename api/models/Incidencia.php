<?php
class Incidencia {
    private $conn;
    private $table_name = "incidencias";

    // Propiedades del objeto
    public $id_incidencia;
    public $titulo;
    public $descripcion;
    public $respuesta_solucion;
    public $id_tipo_incidencia;
    public $id_subtipo_incidencia; // NUEVO
    public $id_usuario_reporta;
    public $nombre_reporta;
    public $email_reporta;
    public $estado;
    public $prioridad;
    public $fecha_reporte;
    public $fecha_cierre;
    public $id_usuario_tecnico;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Leer incidencias con información relacionada incluyendo subtipos
    function read() {
        $query = "SELECT 
                    i.id_incidencia,
                    i.titulo,
                    i.descripcion,
                    i.respuesta_solucion,
                    i.id_tipo_incidencia,
                    i.id_subtipo_incidencia,
                    i.id_usuario_reporta,
                    i.nombre_reporta,
                    i.email_reporta,
                    i.estado,
                    i.confirmacion_usuario,
                    i.comentario_usuario,
                    i.fecha_confirmacion,
                    i.prioridad,
                    i.fecha_reporte,
                    i.fecha_cierre,
                    i.id_usuario_tecnico,
                    ti.nombre as tipo_nombre,
                    si.nombre as subtipo_nombre,
                    ur.nombre_completo as reporta_usuario,
                    ut.nombre_completo as tecnico_asignado,
                    a.nombre_area,
                    s.nombre_sede
                  FROM " . $this->table_name . " i
                  LEFT JOIN tipos_incidencia ti ON i.id_tipo_incidencia = ti.id_tipo_incidencia
                  LEFT JOIN subtipos_incidencias si ON i.id_subtipo_incidencia = si.id_subtipo_incidencia
                  LEFT JOIN usuarios ur ON i.id_usuario_reporta = ur.id_usuario
                  LEFT JOIN usuarios ut ON i.id_usuario_tecnico = ut.id_usuario
                  LEFT JOIN areas a ON ur.id_area = a.id_area
                  LEFT JOIN sedes s ON a.id_sede = s.id_sede
                  ORDER BY i.fecha_reporte DESC";

        $stmt = $this->conn->prepare($query);

        try {
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en Incidencia::read(): " . $e->getMessage());
            return false;
        }
    }

    // Crear incidencia con subtipo
    function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET titulo=:titulo, 
                      descripcion=:descripcion, 
                      id_tipo_incidencia=:id_tipo_incidencia,
                      id_subtipo_incidencia=:id_subtipo_incidencia,
                      id_usuario_reporta=:id_usuario_reporta, 
                      nombre_reporta=:nombre_reporta,
                      email_reporta=:email_reporta, 
                      estado=:estado, 
                      prioridad=:prioridad";

        $stmt = $this->conn->prepare($query);

        // Sanear datos
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->prioridad = htmlspecialchars(strip_tags($this->prioridad));
        $this->nombre_reporta = htmlspecialchars(strip_tags($this->nombre_reporta));
        $this->email_reporta = htmlspecialchars(strip_tags($this->email_reporta));

        // Enlazar valores
        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":id_tipo_incidencia", $this->id_tipo_incidencia);
        $stmt->bindParam(":id_subtipo_incidencia", $this->id_subtipo_incidencia); // NUEVO
        $stmt->bindParam(":id_usuario_reporta", $this->id_usuario_reporta);
        $stmt->bindParam(":nombre_reporta", $this->nombre_reporta);
        $stmt->bindParam(":email_reporta", $this->email_reporta);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":prioridad", $this->prioridad);

        try {
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en Incidencia::create(): " . $e->getMessage());
            return false;
        }
    }

    // Actualizar incidencia con subtipo
    function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET titulo = :titulo,
                  descripcion = :descripcion,
                  respuesta_solucion = :respuesta_solucion,
                  id_tipo_incidencia = :id_tipo_incidencia,
                  id_subtipo_incidencia = :id_subtipo_incidencia,
                  estado = :estado,
                  prioridad = :prioridad,
                  id_usuario_tecnico = :id_usuario_tecnico,
                  nombre_reporta = :nombre_reporta,
                  email_reporta = :email_reporta";

        // Si se está cerrando la incidencia, actualizar fecha de cierre
        if ($this->estado === 'cerrada') {
            $query .= ", fecha_cierre = NOW()";
        }

        $query .= " WHERE id_incidencia = :id_incidencia";

        $stmt = $this->conn->prepare($query);

        // Sanear datos
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->respuesta_solucion = htmlspecialchars(strip_tags($this->respuesta_solucion));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->prioridad = htmlspecialchars(strip_tags($this->prioridad));
        $this->nombre_reporta = htmlspecialchars(strip_tags($this->nombre_reporta));
        $this->email_reporta = htmlspecialchars(strip_tags($this->email_reporta));
        $this->id_incidencia = htmlspecialchars(strip_tags($this->id_incidencia));

        // Enlazar valores
        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':respuesta_solucion', $this->respuesta_solucion);
        $stmt->bindParam(':id_tipo_incidencia', $this->id_tipo_incidencia);
        $stmt->bindParam(':id_subtipo_incidencia', $this->id_subtipo_incidencia); // NUEVO
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':prioridad', $this->prioridad);
        $stmt->bindParam(':id_usuario_tecnico', $this->id_usuario_tecnico);
        $stmt->bindParam(':nombre_reporta', $this->nombre_reporta);
        $stmt->bindParam(':email_reporta', $this->email_reporta);
        $stmt->bindParam(':id_incidencia', $this->id_incidencia);

        try {
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en Incidencia::update(): " . $e->getMessage());
            return false;
        }
    }

    // Eliminar incidencia
    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_incidencia = ?";
        $stmt = $this->conn->prepare($query);
        $this->id_incidencia = htmlspecialchars(strip_tags($this->id_incidencia));
        $stmt->bindParam(1, $this->id_incidencia);

        try {
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en Incidencia::delete(): " . $e->getMessage());
            return false;
        }
    }

    // Buscar incidencias incluyendo subtipos
    function search($keywords) {
        $query = "SELECT 
                    i.id_incidencia,
                    i.titulo,
                    i.descripcion,
                    i.respuesta_solucion,
                    i.id_tipo_incidencia,
                    i.id_subtipo_incidencia,
                    i.id_usuario_reporta,
                    i.nombre_reporta,
                    i.email_reporta,
                    i.estado,
                    i.confirmacion_usuario,
                    i.comentario_usuario,
                    i.fecha_confirmacion,
                    i.prioridad,
                    i.fecha_reporte,
                    i.fecha_cierre,
                    i.id_usuario_tecnico,
                    ti.nombre as tipo_nombre,
                    si.nombre as subtipo_nombre,
                    ur.nombre_completo as reporta_usuario,
                    ut.nombre_completo as tecnico_asignado
                  FROM " . $this->table_name . " i
                  LEFT JOIN tipos_incidencia ti ON i.id_tipo_incidencia = ti.id_tipo_incidencia
                  LEFT JOIN subtipos_incidencias si ON i.id_subtipo_incidencia = si.id_subtipo_incidencia
                  LEFT JOIN usuarios ur ON i.id_usuario_reporta = ur.id_usuario
                  LEFT JOIN usuarios ut ON i.id_usuario_tecnico = ut.id_usuario
                  WHERE i.titulo LIKE ? OR i.descripcion LIKE ? OR i.nombre_reporta LIKE ?
                  ORDER BY i.fecha_reporte DESC";

        $stmt = $this->conn->prepare($query);

        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";

        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);

        try {
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en Incidencia::search(): " . $e->getMessage());
            return false;
        }
    }

    // Leer una incidencia específica con subtipo
    function readOne() {
        $query = "SELECT 
                    i.id_incidencia,
                    i.titulo,
                    i.descripcion,
                    i.respuesta_solucion,
                    i.id_tipo_incidencia,
                    i.id_subtipo_incidencia,
                    i.id_usuario_reporta,
                    i.nombre_reporta,
                    i.email_reporta,
                    i.estado,
                    i.confirmacion_usuario,
                    i.comentario_usuario,
                    i.fecha_confirmacion,
                    i.prioridad,
                    i.fecha_reporte,
                    i.fecha_cierre,
                    i.id_usuario_tecnico,
                    ti.nombre as tipo_nombre,
                    si.nombre as subtipo_nombre,
                    ur.nombre_completo as reporta_usuario,
                    ut.nombre_completo as tecnico_asignado,
                    a.nombre_area,
                    s.nombre_sede
                  FROM " . $this->table_name . " i
                  LEFT JOIN tipos_incidencia ti ON i.id_tipo_incidencia = ti.id_tipo_incidencia
                  LEFT JOIN subtipos_incidencias si ON i.id_subtipo_incidencia = si.id_subtipo_incidencia
                  LEFT JOIN usuarios ur ON i.id_usuario_reporta = ur.id_usuario
                  LEFT JOIN usuarios ut ON i.id_usuario_tecnico = ut.id_usuario
                  LEFT JOIN areas a ON ur.id_area = a.id_area
                  LEFT JOIN sedes s ON a.id_sede = s.id_sede
                  WHERE i.id_incidencia = ?
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_incidencia);

        try {
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->titulo = $row['titulo'];
                $this->descripcion = $row['descripcion'];
                $this->respuesta_solucion = $row['respuesta_solucion'];
                $this->id_tipo_incidencia = $row['id_tipo_incidencia'];
                $this->id_subtipo_incidencia = $row['id_subtipo_incidencia']; // NUEVO
                $this->id_usuario_reporta = $row['id_usuario_reporta'];
                $this->nombre_reporta = $row['nombre_reporta'];
                $this->email_reporta = $row['email_reporta'];
                $this->estado = $row['estado'];
                $this->prioridad = $row['prioridad'];
                $this->fecha_reporte = $row['fecha_reporte'];
                $this->fecha_cierre = $row['fecha_cierre'];
                $this->id_usuario_tecnico = $row['id_usuario_tecnico'];
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en Incidencia::readOne(): " . $e->getMessage());
            return false;
        }
    }

    // Obtener estadísticas incluyendo subtipos
    function getStats() {
        try {
            $stats = array();

            // Total de incidencias
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total'] = intval($result['total']);

            // Por estado
            $query = "SELECT estado, COUNT(*) as count FROM " . $this->table_name . " GROUP BY estado";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Por prioridad
            $query = "SELECT prioridad, COUNT(*) as count FROM " . $this->table_name . " GROUP BY prioridad";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['por_prioridad'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Por tipo
            $query = "SELECT ti.nombre, COUNT(i.id_incidencia) as count 
                     FROM tipos_incidencia ti 
                     LEFT JOIN " . $this->table_name . " i ON ti.id_tipo_incidencia = i.id_tipo_incidencia 
                     GROUP BY ti.id_tipo_incidencia, ti.nombre";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['por_tipo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // NUEVO: Por subtipo (top 10)
            $query = "SELECT si.nombre, COUNT(i.id_incidencia) as count 
                     FROM subtipos_incidencias si 
                     LEFT JOIN " . $this->table_name . " i ON si.id_subtipo_incidencia = i.id_subtipo_incidencia 
                     WHERE si.estado = 'activo'
                     GROUP BY si.id_subtipo_incidencia, si.nombre
                     ORDER BY count DESC
                     LIMIT 10";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['por_subtipo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $stats;
        } catch (PDOException $e) {
            error_log("Error en Incidencia::getStats(): " . $e->getMessage());
            return false;
        }
    }
    // Leer incidencias por usuario
    function readByUser($userId) {
        $query = "SELECT 
                    i.id_incidencia,
                    i.titulo,
                    i.descripcion,
                    i.respuesta_solucion,
                    i.id_tipo_incidencia,
                    i.id_subtipo_incidencia,
                    i.id_usuario_reporta,
                    i.nombre_reporta,
                    i.email_reporta,
                    i.estado,
                    i.confirmacion_usuario,
                    i.comentario_usuario,
                    i.fecha_confirmacion,
                    i.prioridad,
                    i.fecha_reporte,
                    i.fecha_cierre,
                    i.id_usuario_tecnico,
                    ti.nombre as tipo_nombre,
                    si.nombre as subtipo_nombre,
                    ur.nombre_completo as reporta_usuario,
                    ut.nombre_completo as tecnico_asignado,
                    a.nombre_area,
                    s.nombre_sede
                  FROM " . $this->table_name . " i
                  LEFT JOIN tipos_incidencia ti ON i.id_tipo_incidencia = ti.id_tipo_incidencia
                  LEFT JOIN subtipos_incidencias si ON i.id_subtipo_incidencia = si.id_subtipo_incidencia
                  LEFT JOIN usuarios ur ON i.id_usuario_reporta = ur.id_usuario
                  LEFT JOIN usuarios ut ON i.id_usuario_tecnico = ut.id_usuario
                  LEFT JOIN areas a ON ur.id_area = a.id_area
                  LEFT JOIN sedes s ON a.id_sede = s.id_sede
                  WHERE i.id_usuario_reporta = ? 
                     OR (i.id_usuario_reporta IS NULL AND i.nombre_reporta = (SELECT nombre_completo FROM usuarios WHERE id_usuario = ?))
                  ORDER BY i.fecha_reporte DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $userId);
        $stmt->bindParam(2, $userId);

        try {
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en Incidencia::readByUser(): " . $e->getMessage());
            return false;
        }
    }

    // Obtener estadísticas por usuario
    function getStatsByUser($userId) {
        try {
            $stats = array();

            // Total de incidencias del usuario
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                     WHERE id_usuario_reporta = ? 
                     OR (id_usuario_reporta IS NULL AND nombre_reporta = (SELECT nombre_completo FROM usuarios WHERE id_usuario = ?))";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $userId);
            $stmt->bindParam(2, $userId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total'] = intval($result['total']);

            // Por estado
            $query = "SELECT estado, COUNT(*) as count FROM " . $this->table_name . " 
                     WHERE id_usuario_reporta = ? 
                     OR (id_usuario_reporta IS NULL AND nombre_reporta = (SELECT nombre_completo FROM usuarios WHERE id_usuario = ?))
                     GROUP BY estado";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $userId);
            $stmt->bindParam(2, $userId);
            $stmt->execute();
            $stats['por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Por prioridad
            $query = "SELECT prioridad, COUNT(*) as count FROM " . $this->table_name . " 
                     WHERE id_usuario_reporta = ? 
                     OR (id_usuario_reporta IS NULL AND nombre_reporta = (SELECT nombre_completo FROM usuarios WHERE id_usuario = ?))
                     GROUP BY prioridad";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $userId);
            $stmt->bindParam(2, $userId);
            $stmt->execute();
            $stats['por_prioridad'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $stats;
        } catch (PDOException $e) {
            error_log("Error en Incidencia::getStatsByUser(): " . $e->getMessage());
            return false;
        }
    }

    // Confirmar solución de incidencia
    function confirmarSolucion($confirmacion, $comentario) {
        $query = "UPDATE " . $this->table_name . "
                  SET confirmacion_usuario = :confirmacion,
                      comentario_usuario = :comentario,
                      fecha_confirmacion = NOW()";

        // Si el usuario confirma que está solucionado, cerramos la incidencia
        if ($confirmacion === 'solucionado') {
            $query .= ", estado = 'cerrada', fecha_cierre = NOW()";
        } 
        // Si dice que NO está solucionado, la reabrimos (o cambiamos a en_proceso/abierta)
        else if ($confirmacion === 'no_solucionado') {
            $query .= ", estado = 'en_proceso'";
        }

        $query .= " WHERE id_incidencia = :id_incidencia";

        $stmt = $this->conn->prepare($query);

        // Sanear datos
        $confirmacion = htmlspecialchars(strip_tags($confirmacion));
        $comentario = htmlspecialchars(strip_tags($comentario));
        $this->id_incidencia = htmlspecialchars(strip_tags($this->id_incidencia));

        // Enlazar valores
        $stmt->bindParam(':confirmacion', $confirmacion);
        $stmt->bindParam(':comentario', $comentario);
        $stmt->bindParam(':id_incidencia', $this->id_incidencia);

        try {
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en Incidencia::confirmarSolucion(): " . $e->getMessage());
            return false;
        }
    }
}
?>