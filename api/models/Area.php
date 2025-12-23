<?php
// Se requiere el archivo de conexión a la base de datos
require_once 'database.php';

class Area {
    // Conexión a la base de datos y nombre de la tabla
    private $conn;
    private $table_name = "areas";

    // Propiedades del objeto
    public $id_area;
    public $nombre_area;
    public $id_sede;

    // Constructor con $db como conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Lee todas las areas con información de la sede
     * @return PDOStatement|false
     */
    public function read() {
        try {
            $query = "SELECT 
                        a.id_area, 
                        a.nombre_area, 
                        a.id_sede,
                        s.nombre_sede
                      FROM " . $this->table_name . " a
                      LEFT JOIN sedes s ON a.id_sede = s.id_sede
                      ORDER BY s.nombre_sede ASC, a.nombre_area ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en Area::read(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lee areas por sede específica
     * @param int $id_sede ID de la sede
     * @return PDOStatement|false
     */
    public function readBySede($id_sede) {
        try {
            $query = "SELECT 
                        id_area, 
                        nombre_area, 
                        id_sede
                      FROM " . $this->table_name . " 
                      WHERE id_sede = ? 
                      ORDER BY nombre_area ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id_sede, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en Area::readBySede(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea una nueva area
     * @return bool True si el area se crea exitosamente, de lo contrario False
     */
    public function create() {
        try {
            // Validar datos requeridos
            if (empty(trim($this->nombre_area))) {
                error_log("Error en Area::create(): nombre_area está vacío");
                return false;
            }

            if (empty($this->id_sede)) {
                error_log("Error en Area::create(): id_sede está vacío");
                return false;
            }

            $query = "INSERT INTO " . $this->table_name . " 
                      SET nombre_area=:nombre_area, 
                          id_sede=:id_sede";

            $stmt = $this->conn->prepare($query);

            // Limpia los datos
            $this->nombre_area = htmlspecialchars(strip_tags(trim($this->nombre_area)));
            $this->id_sede = intval($this->id_sede);

            // Vincula los valores
            $stmt->bindParam(":nombre_area", $this->nombre_area);
            $stmt->bindParam(":id_sede", $this->id_sede, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Area::create(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lee una única area
     * @return bool True si el area existe, de lo contrario False
     */
    public function readOne() {
        try {
            // Validar que se haya proporcionado un ID
            if (empty($this->id_area)) {
                error_log("Error en Area::readOne(): id_area está vacío");
                return false;
            }

            $query = "SELECT 
                        a.nombre_area, 
                        a.id_sede,
                        s.nombre_sede
                      FROM " . $this->table_name . " a
                      LEFT JOIN sedes s ON a.id_sede = s.id_sede
                      WHERE a.id_area = ? 
                      LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id_area, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->nombre_area = $row['nombre_area'];
                $this->id_sede = $row['id_sede'];
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en Area::readOne(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un area existente
     * @return bool True si la actualización fue exitosa, de lo contrario False
     */
    public function update() {
        try {
            // Validar datos requeridos
            if (empty($this->id_area) || empty(trim($this->nombre_area)) || empty($this->id_sede)) {
                error_log("Error en Area::update(): Datos requeridos faltantes");
                return false;
            }

            $query = "UPDATE " . $this->table_name . " 
                      SET nombre_area = :nombre_area, 
                          id_sede = :id_sede 
                      WHERE id_area = :id_area";

            $stmt = $this->conn->prepare($query);

            // Limpia los datos
            $this->nombre_area = htmlspecialchars(strip_tags(trim($this->nombre_area)));
            $this->id_area = intval($this->id_area);
            $this->id_sede = intval($this->id_sede);

            // Vincula los valores
            $stmt->bindParam(':nombre_area', $this->nombre_area);
            $stmt->bindParam(':id_sede', $this->id_sede, PDO::PARAM_INT);
            $stmt->bindParam(':id_area', $this->id_area, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Area::update(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un area
     * @return bool True si la eliminación fue exitosa, de lo contrario False
     */
    public function delete() {
        try {
            // Validar que se haya proporcionado un ID
            if (empty($this->id_area)) {
                error_log("Error en Area::delete(): id_area está vacío");
                return false;
            }

            $query = "DELETE FROM " . $this->table_name . " WHERE id_area = ?";

            $stmt = $this->conn->prepare($query);

            // Limpia el ID y asegura que sea un entero
            $this->id_area = intval($this->id_area);

            // Vincula el ID
            $stmt->bindParam(1, $this->id_area, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Area::delete(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si existe un area con el nombre dado en la misma sede
     * @param string $nombre_area Nombre del area a verificar
     * @param int $id_sede ID de la sede
     * @param int|null $exclude_id ID a excluir de la búsqueda (útil para updates)
     * @return bool True si existe, False si no existe
     */
    public function existeNombre($nombre_area, $id_sede, $exclude_id = null) {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name . " 
                      WHERE nombre_area = ? AND id_sede = ?";
            $params = [trim($nombre_area), intval($id_sede)];

            if ($exclude_id) {
                $query .= " AND id_area != ?";
                $params[] = intval($exclude_id);
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en Area::existeNombre(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el total de areas
     * @return int Número total de areas
     */
    public function count() {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Area::count(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene todas las sedes para llenar selectores
     * @return PDOStatement|false
     */
    public function getSedes() {
        try {
            $query = "SELECT id_sede, nombre_sede FROM sedes ORDER BY nombre_sede ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en Area::getSedes(): " . $e->getMessage());
            return false;
        }
    }
}
?>