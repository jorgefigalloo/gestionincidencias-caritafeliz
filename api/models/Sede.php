<?php
// Se requiere el archivo de conexión a la base de datos
require_once 'database.php';

class Sede {
    // Conexión a la base de datos y nombre de la tabla
    private $conn;
    private $table_name = "sedes";

    // Propiedades del objeto, usando nombre_sede para coincidir con la BD
    public $id_sede;
    public $nombre_sede;
    public $descripcion;

    // Constructor con $db como conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Lee todas las sedes
     * @return PDOStatement|false
     */
    public function read() {
        try {
            // La consulta ahora busca 'nombre_sede'
            $query = "SELECT id_sede, nombre_sede, descripcion FROM " . $this->table_name . " ORDER BY nombre_sede ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en Sede::read(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea una nueva sede
     * @return bool True si la sede se crea exitosamente, de lo contrario False
     */
    public function create() {
        try {
            // Validar datos requeridos
            if (empty(trim($this->nombre_sede))) {
                error_log("Error en Sede::create(): nombre_sede está vacío");
                return false;
            }

            // La consulta ahora usa 'nombre_sede'
            $query = "INSERT INTO " . $this->table_name . " SET nombre_sede=:nombre_sede, descripcion=:descripcion";

            $stmt = $this->conn->prepare($query);

            // Limpia los datos
            $this->nombre_sede = htmlspecialchars(strip_tags(trim($this->nombre_sede)));
            $this->descripcion = htmlspecialchars(strip_tags(trim($this->descripcion)));

            // Vincula los valores
            $stmt->bindParam(":nombre_sede", $this->nombre_sede);
            $stmt->bindParam(":descripcion", $this->descripcion);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Sede::create(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lee una única sede
     * @return bool True si la sede existe, de lo contrario False
     */
    public function readOne() {
        try {
            // Validar que se haya proporcionado un ID
            if (empty($this->id_sede)) {
                error_log("Error en Sede::readOne(): id_sede está vacío");
                return false;
            }

            // La consulta ahora usa 'nombre_sede'
            $query = "SELECT nombre_sede, descripcion FROM " . $this->table_name . " WHERE id_sede = ? LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id_sede, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->nombre_sede = $row['nombre_sede'];
                $this->descripcion = $row['descripcion'];
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en Sede::readOne(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una sede existente
     * @return bool True si la actualización fue exitosa, de lo contrario False
     */
    public function update() {
        try {
            // Validar datos requeridos
            if (empty($this->id_sede) || empty(trim($this->nombre_sede))) {
                error_log("Error en Sede::update(): Datos requeridos faltantes");
                return false;
            }

            // La consulta ahora usa 'nombre_sede'
            $query = "UPDATE " . $this->table_name . " SET nombre_sede = :nombre_sede, descripcion = :descripcion WHERE id_sede = :id_sede";

            $stmt = $this->conn->prepare($query);

            // Limpia los datos
            $this->nombre_sede = htmlspecialchars(strip_tags(trim($this->nombre_sede)));
            $this->descripcion = htmlspecialchars(strip_tags(trim($this->descripcion)));
            $this->id_sede = intval($this->id_sede); // Asegurar que sea un entero

            // Vincula los valores
            $stmt->bindParam(':nombre_sede', $this->nombre_sede);
            $stmt->bindParam(':descripcion', $this->descripcion);
            $stmt->bindParam(':id_sede', $this->id_sede, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Sede::update(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina una sede
     * @return bool True si la eliminación fue exitosa, de lo contrario False
     */
    public function delete() {
        try {
            // Validar que se haya proporcionado un ID
            if (empty($this->id_sede)) {
                error_log("Error en Sede::delete(): id_sede está vacío");
                return false;
            }

            $query = "DELETE FROM " . $this->table_name . " WHERE id_sede = ?";

            $stmt = $this->conn->prepare($query);

            // Limpia el ID y asegura que sea un entero
            $this->id_sede = intval($this->id_sede);

            // Vincula el ID
            $stmt->bindParam(1, $this->id_sede, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Sede::delete(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si existe una sede con el nombre dado
     * @param string $nombre_sede Nombre de la sede a verificar
     * @param int|null $exclude_id ID a excluir de la búsqueda (útil para updates)
     * @return bool True si existe, False si no existe
     */
    public function existeNombre($nombre_sede, $exclude_id = null) {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE nombre_sede = ?";
            $params = [trim($nombre_sede)];

            if ($exclude_id) {
                $query .= " AND id_sede != ?";
                $params[] = intval($exclude_id);
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en Sede::existeNombre(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el total de sedes
     * @return int Número total de sedes
     */
    public function count() {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Sede::count(): " . $e->getMessage());
            return 0;
        }
    }
}
?>