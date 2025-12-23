<?php
// api/models/SubtipoIncidencia.php
class SubtipoIncidencia {
    private $conn;
    private $table_name = "subtipos_incidencias";

    public $id_subtipo_incidencia;
    public $nombre;
    public $descripcion;
    public $id_tipo_incidencia;
    public $estado;
    public $fecha_creacion;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Lee todos los subtipos de incidencia
     */
    public function read() {
        try {
            $query = "SELECT 
                        s.id_subtipo_incidencia,
                        s.nombre,
                        s.descripcion,
                        s.id_tipo_incidencia,
                        s.estado,
                        s.fecha_creacion,
                        t.nombre as tipo_nombre
                      FROM " . $this->table_name . " s
                      INNER JOIN tipos_incidencia t ON s.id_tipo_incidencia = t.id_tipo_incidencia
                      ORDER BY t.nombre ASC, s.nombre ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en SubtipoIncidencia::read(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lee subtipos por tipo de incidencia
     */
    public function readByTipo($id_tipo) {
        try {
            $query = "SELECT 
                        id_subtipo_incidencia,
                        nombre,
                        descripcion,
                        estado
                      FROM " . $this->table_name . "
                      WHERE id_tipo_incidencia = :id_tipo 
                      AND estado = 'activo'
                      ORDER BY nombre ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_tipo', $id_tipo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en SubtipoIncidencia::readByTipo(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lee un subtipo específico
     */
    public function readOne() {
        try {
            if (empty($this->id_subtipo_incidencia)) {
                return false;
            }

            $query = "SELECT 
                        s.id_subtipo_incidencia,
                        s.nombre,
                        s.descripcion,
                        s.id_tipo_incidencia,
                        s.estado,
                        s.fecha_creacion,
                        t.nombre as tipo_nombre
                      FROM " . $this->table_name . " s
                      INNER JOIN tipos_incidencia t ON s.id_tipo_incidencia = t.id_tipo_incidencia
                      WHERE s.id_subtipo_incidencia = ?
                      LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id_subtipo_incidencia, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->nombre = $row['nombre'];
                $this->descripcion = $row['descripcion'];
                $this->id_tipo_incidencia = $row['id_tipo_incidencia'];
                $this->estado = $row['estado'];
                $this->fecha_creacion = $row['fecha_creacion'];
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en SubtipoIncidencia::readOne(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo subtipo
     */
    public function create() {
        try {
            if (empty(trim($this->nombre)) || empty($this->id_tipo_incidencia)) {
                return false;
            }

            $query = "INSERT INTO " . $this->table_name . "
                      SET nombre = :nombre,
                          descripcion = :descripcion,
                          id_tipo_incidencia = :id_tipo_incidencia,
                          estado = :estado";

            $stmt = $this->conn->prepare($query);

            $this->nombre = htmlspecialchars(strip_tags(trim($this->nombre)));
            $this->descripcion = htmlspecialchars(strip_tags(trim($this->descripcion)));
            $this->estado = !empty($this->estado) ? $this->estado : 'activo';

            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->bindParam(':id_tipo_incidencia', $this->id_tipo_incidencia, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $this->estado);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en SubtipoIncidencia::create(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un subtipo existente
     */
    public function update() {
        try {
            if (empty($this->id_subtipo_incidencia) || empty(trim($this->nombre))) {
                return false;
            }

            $query = "UPDATE " . $this->table_name . "
                      SET nombre = :nombre,
                          descripcion = :descripcion,
                          id_tipo_incidencia = :id_tipo_incidencia,
                          estado = :estado
                      WHERE id_subtipo_incidencia = :id_subtipo_incidencia";

            $stmt = $this->conn->prepare($query);

            $this->nombre = htmlspecialchars(strip_tags(trim($this->nombre)));
            $this->descripcion = htmlspecialchars(strip_tags(trim($this->descripcion)));
            $this->id_subtipo_incidencia = intval($this->id_subtipo_incidencia);

            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->bindParam(':id_tipo_incidencia', $this->id_tipo_incidencia, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $this->estado);
            $stmt->bindParam(':id_subtipo_incidencia', $this->id_subtipo_incidencia, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en SubtipoIncidencia::update(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un subtipo
     */
    public function delete() {
        try {
            if (empty($this->id_subtipo_incidencia)) {
                return false;
            }

            // Verificar si está en uso
            if ($this->isInUse()) {
                return false;
            }

            $query = "DELETE FROM " . $this->table_name . " 
                      WHERE id_subtipo_incidencia = ?";

            $stmt = $this->conn->prepare($query);
            $this->id_subtipo_incidencia = intval($this->id_subtipo_incidencia);
            $stmt->bindParam(1, $this->id_subtipo_incidencia, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en SubtipoIncidencia::delete(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si el subtipo está en uso
     */
    public function isInUse() {
        try {
            if (empty($this->id_subtipo_incidencia)) {
                return false;
            }

            $query = "SELECT COUNT(*) FROM incidencias 
                      WHERE id_subtipo_incidencia = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id_subtipo_incidencia, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en SubtipoIncidencia::isInUse(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cuenta subtipos por tipo
     */
    public function countByTipo($id_tipo) {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name . "
                      WHERE id_tipo_incidencia = ? AND estado = 'activo'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id_tipo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Obtiene estadísticas de uso
     */
    public function getUsageStats() {
        try {
            $query = "SELECT 
                        s.id_subtipo_incidencia,
                        s.nombre,
                        t.nombre as tipo_nombre,
                        COUNT(i.id_incidencia) as total_incidencias,
                        SUM(CASE WHEN i.estado = 'abierta' THEN 1 ELSE 0 END) as abiertas,
                        SUM(CASE WHEN i.estado = 'cerrada' THEN 1 ELSE 0 END) as cerradas
                      FROM " . $this->table_name . " s
                      INNER JOIN tipos_incidencia t ON s.id_tipo_incidencia = t.id_tipo_incidencia
                      LEFT JOIN incidencias i ON s.id_subtipo_incidencia = i.id_subtipo_incidencia
                      GROUP BY s.id_subtipo_incidencia, s.nombre, t.nombre
                      ORDER BY total_incidencias DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en SubtipoIncidencia::getUsageStats(): " . $e->getMessage());
            return array();
        }
    }
}
?>