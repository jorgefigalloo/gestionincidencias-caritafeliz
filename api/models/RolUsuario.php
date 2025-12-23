<?php
// Se requiere el archivo de conexión a la base de datos
require_once 'database.php';

class RolUsuario {
    // Conexión a la base de datos y nombre de la tabla
    private $conn;
    private $table_name = "rol_usuario";

    // Propiedades del objeto
    public $id_rol;
    public $nombre_rol;

    // Constructor con $db como conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Lee todos los roles de usuario
     * @return PDOStatement|false
     */
    public function read() {
        try {
            $query = "SELECT 
                        id_rol, 
                        nombre_rol
                      FROM " . $this->table_name . " 
                      ORDER BY nombre_rol ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en RolUsuario::read(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo rol de usuario
     * @return bool True si el rol se crea exitosamente, de lo contrario False
     */
    public function create() {
        try {
            // Validar datos requeridos
            if (empty(trim($this->nombre_rol))) {
                error_log("Error en RolUsuario::create(): nombre_rol está vacío");
                return false;
            }

            $query = "INSERT INTO " . $this->table_name . " 
                      SET nombre_rol=:nombre_rol";

            $stmt = $this->conn->prepare($query);

            // Limpia los datos
            $this->nombre_rol = htmlspecialchars(strip_tags(trim($this->nombre_rol)));

            // Vincula los valores
            $stmt->bindParam(":nombre_rol", $this->nombre_rol);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en RolUsuario::create(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lee un único rol de usuario
     * @return bool True si el rol existe, de lo contrario False
     */
    public function readOne() {
        try {
            // Validar que se haya proporcionado un ID
            if (empty($this->id_rol)) {
                error_log("Error en RolUsuario::readOne(): id_rol está vacío");
                return false;
            }

            $query = "SELECT nombre_rol
                      FROM " . $this->table_name . " 
                      WHERE id_rol = ? 
                      LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id_rol, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->nombre_rol = $row['nombre_rol'];
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en RolUsuario::readOne(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un rol de usuario existente
     * @return bool True si la actualización fue exitosa, de lo contrario False
     */
    public function update() {
        try {
            // Validar datos requeridos
            if (empty($this->id_rol) || empty(trim($this->nombre_rol))) {
                error_log("Error en RolUsuario::update(): Datos requeridos faltantes");
                return false;
            }

            $query = "UPDATE " . $this->table_name . " 
                      SET nombre_rol = :nombre_rol 
                      WHERE id_rol = :id_rol";

            $stmt = $this->conn->prepare($query);

            // Limpia los datos
            $this->nombre_rol = htmlspecialchars(strip_tags(trim($this->nombre_rol)));
            $this->id_rol = intval($this->id_rol);

            // Vincula los valores
            $stmt->bindParam(':nombre_rol', $this->nombre_rol);
            $stmt->bindParam(':id_rol', $this->id_rol, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en RolUsuario::update(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un rol de usuario
     * @return bool True si la eliminación fue exitosa, de lo contrario False
     */
    public function delete() {
        try {
            // Validar que se haya proporcionado un ID
            if (empty($this->id_rol)) {
                error_log("Error en RolUsuario::delete(): id_rol está vacío");
                return false;
            }

            // Verificar si el rol está siendo usado por algún usuario
            if ($this->isInUse()) {
                error_log("Error en RolUsuario::delete(): El rol está siendo usado por usuarios");
                return false;
            }

            $query = "DELETE FROM " . $this->table_name . " WHERE id_rol = ?";

            $stmt = $this->conn->prepare($query);

            // Limpia el ID y asegura que sea un entero
            $this->id_rol = intval($this->id_rol);

            // Vincula el ID
            $stmt->bindParam(1, $this->id_rol, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en RolUsuario::delete(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si existe un rol con el nombre dado
     * @param string $nombre_rol Nombre del rol a verificar
     * @param int|null $exclude_id ID a excluir de la búsqueda (útil para updates)
     * @return bool True si existe, False si no existe
     */
    public function existeNombre($nombre_rol, $exclude_id = null) {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name . " 
                      WHERE nombre_rol = ?";
            $params = [trim($nombre_rol)];

            if ($exclude_id) {
                $query .= " AND id_rol != ?";
                $params[] = intval($exclude_id);
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en RolUsuario::existeNombre(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si el rol está siendo usado por algún usuario
     * @return bool True si está en uso, False si no está en uso
     */
    public function isInUse() {
        try {
            if (empty($this->id_rol)) {
                return false;
            }

            $query = "SELECT COUNT(*) FROM usuarios WHERE ID_ROL_USUARIO = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id_rol, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en RolUsuario::isInUse(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el total de roles
     * @return int Número total de roles
     */
    public function count() {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en RolUsuario::count(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene estadísticas de uso de roles
     * @return array Array con estadísticas por rol
     */
    public function getUsageStats() {
        try {
            $query = "SELECT 
                        r.id_rol,
                        r.nombre_rol,
                        COUNT(u.id_usuario) as usuarios_count
                      FROM " . $this->table_name . " r
                      LEFT JOIN usuarios u ON r.id_rol = u.ID_ROL_USUARIO
                      GROUP BY r.id_rol, r.nombre_rol
                      ORDER BY r.nombre_rol ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en RolUsuario::getUsageStats(): " . $e->getMessage());
            return array();
        }
    }

    /**
     * Valida si el nombre del rol es válido
     * @param string $nombre_rol Nombre del rol a validar
     * @return array Array con el resultado de la validación
     */
    public function validateRoleName($nombre_rol) {
        $result = array('valid' => true, 'errors' => array());
        
        $nombre_rol = trim($nombre_rol);
        
        // Verificar que no esté vacío
        if (empty($nombre_rol)) {
            $result['valid'] = false;
            $result['errors'][] = 'El nombre del rol es requerido';
        }
        
        // Verificar longitud
        if (strlen($nombre_rol) > 20) {
            $result['valid'] = false;
            $result['errors'][] = 'El nombre del rol no puede exceder 20 caracteres';
        }
        
        // Verificar caracteres válidos (solo letras, números y guiones bajos)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $nombre_rol)) {
            $result['valid'] = false;
            $result['errors'][] = 'El nombre del rol solo puede contener letras, números y guiones bajos';
        }
        
        return $result;
    }
}
?>