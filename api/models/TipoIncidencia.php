<?php
class TipoIncidencia {
    // Conexión a la base de datos y nombre de la tabla
    private $conn;
    private $table_name = "tipos_incidencia";

    // Propiedades del objeto
    public $id_tipo_incidencia;
    public $nombre;

    // Constructor con $db como conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Lee todos los tipos de incidencia
     * @return PDOStatement|false
     */
    public function read() {
        try {
            $query = "SELECT 
                        id_tipo_incidencia, 
                        nombre
                      FROM " . $this->table_name . " 
                      ORDER BY nombre ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en TipoIncidencia::read(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo tipo de incidencia
     * @return bool True si el tipo se crea exitosamente, de lo contrario False
     */
    public function create() {
        try {
            // Validar datos requeridos
            if (empty(trim($this->nombre))) {
                error_log("Error en TipoIncidencia::create(): nombre está vacío");
                return false;
            }

            $query = "INSERT INTO " . $this->table_name . " 
                      SET nombre=:nombre";

            $stmt = $this->conn->prepare($query);

            // Limpia los datos
            $this->nombre = htmlspecialchars(strip_tags(trim($this->nombre)));

            // Vincula los valores
            $stmt->bindParam(":nombre", $this->nombre);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en TipoIncidencia::create(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lee un único tipo de incidencia
     * @return bool True si el tipo existe, de lo contrario False
     */
    public function readOne() {
        try {
            // Validar que se haya proporcionado un ID
            if (empty($this->id_tipo_incidencia)) {
                error_log("Error en TipoIncidencia::readOne(): id_tipo_incidencia está vacío");
                return false;
            }

            $query = "SELECT nombre
                      FROM " . $this->table_name . " 
                      WHERE id_tipo_incidencia = ? 
                      LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id_tipo_incidencia, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->nombre = $row['nombre'];
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en TipoIncidencia::readOne(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un tipo de incidencia existente
     * @return bool True si la actualización fue exitosa, de lo contrario False
     */
    public function update() {
        try {
            // Validar datos requeridos
            if (empty($this->id_tipo_incidencia) || empty(trim($this->nombre))) {
                error_log("Error en TipoIncidencia::update(): Datos requeridos faltantes");
                return false;
            }

            $query = "UPDATE " . $this->table_name . " 
                      SET nombre = :nombre 
                      WHERE id_tipo_incidencia = :id_tipo_incidencia";

            $stmt = $this->conn->prepare($query);

            // Limpia los datos
            $this->nombre = htmlspecialchars(strip_tags(trim($this->nombre)));
            $this->id_tipo_incidencia = intval($this->id_tipo_incidencia);

            // Vincula los valores
            $stmt->bindParam(':nombre', $this->nombre);
            $stmt->bindParam(':id_tipo_incidencia', $this->id_tipo_incidencia, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en TipoIncidencia::update(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un tipo de incidencia
     * @return bool True si la eliminación fue exitosa, de lo contrario False
     */
    public function delete() {
        try {
            // Validar que se haya proporcionado un ID
            if (empty($this->id_tipo_incidencia)) {
                error_log("Error en TipoIncidencia::delete(): id_tipo_incidencia está vacío");
                return false;
            }

            // Verificar si el tipo está siendo usado por alguna incidencia
            if ($this->isInUse()) {
                error_log("Error en TipoIncidencia::delete(): El tipo está siendo usado por incidencias");
                return false;
            }

            $query = "DELETE FROM " . $this->table_name . " WHERE id_tipo_incidencia = ?";

            $stmt = $this->conn->prepare($query);

            // Limpia el ID y asegura que sea un entero
            $this->id_tipo_incidencia = intval($this->id_tipo_incidencia);

            // Vincula el ID
            $stmt->bindParam(1, $this->id_tipo_incidencia, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en TipoIncidencia::delete(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si existe un tipo con el nombre dado
     * @param string $nombre Nombre del tipo a verificar
     * @param int|null $exclude_id ID a excluir de la búsqueda (útil para updates)
     * @return bool True si existe, False si no existe
     */
    public function existeNombre($nombre, $exclude_id = null) {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name . " 
                      WHERE nombre = ?";
            $params = [trim($nombre)];

            if ($exclude_id) {
                $query .= " AND id_tipo_incidencia != ?";
                $params[] = intval($exclude_id);
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en TipoIncidencia::existeNombre(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si el tipo está siendo usado por alguna incidencia
     * @return bool True si está en uso, False si no está en uso
     */
    public function isInUse() {
        try {
            if (empty($this->id_tipo_incidencia)) {
                return false;
            }

            $query = "SELECT COUNT(*) FROM incidencias WHERE id_tipo_incidencia = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id_tipo_incidencia, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en TipoIncidencia::isInUse(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el total de tipos de incidencia
     * @return int Número total de tipos
     */
    public function count() {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en TipoIncidencia::count(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene estadísticas de uso de tipos de incidencia
     * @return array Array con estadísticas por tipo
     */
    public function getUsageStats() {
        try {
            $query = "SELECT 
                        t.id_tipo_incidencia,
                        t.nombre,
                        COUNT(i.id_incidencia) as incidencias_count,
                        SUM(CASE WHEN i.estado = 'abierta' THEN 1 ELSE 0 END) as abiertas,
                        SUM(CASE WHEN i.estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
                        SUM(CASE WHEN i.estado = 'cerrada' THEN 1 ELSE 0 END) as cerradas,
                        SUM(CASE WHEN i.estado = 'cancelada' THEN 1 ELSE 0 END) as canceladas
                      FROM " . $this->table_name . " t
                      LEFT JOIN incidencias i ON t.id_tipo_incidencia = i.id_tipo_incidencia
                      GROUP BY t.id_tipo_incidencia, t.nombre
                      ORDER BY incidencias_count DESC, t.nombre ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en TipoIncidencia::getUsageStats(): " . $e->getMessage());
            return array();
        }
    }
}
?>