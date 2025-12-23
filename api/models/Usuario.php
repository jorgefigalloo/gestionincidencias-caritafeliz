<?php
// Se requiere el archivo de conexión a la base de datos
// require_once __DIR__ . '/database.php';

class Usuario {
    // Conexión a la base de datos y nombre de la tabla
    private $conn;
    private $table_name = "usuarios";

    // Propiedades del objeto
    public $id_usuario;
    public $nombre_completo;
    public $email;
    public $notificaciones_activas;
    public $username;
    public $password;
    public $ID_ROL_USUARIO;
    public $id_area;
    public $estado;

    // Constructor con $db como conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Lee todos los usuarios con información relacionada
     * @return PDOStatement|false
     */
    public function read() {
        try {
            $query = "SELECT 
                        u.id_usuario,
                        u.nombre_completo,
                        u.email,
                        u.notificaciones_activas,
                        u.username,
                        u.ID_ROL_USUARIO,
                        u.id_area,
                        u.estado,
                        r.nombre_rol,
                        a.nombre_area,
                        s.nombre_sede
                      FROM " . $this->table_name . " u
                      LEFT JOIN rol_usuario r ON u.ID_ROL_USUARIO = r.id_rol
                      LEFT JOIN areas a ON u.id_area = a.id_area
                      LEFT JOIN sedes s ON a.id_sede = s.id_sede
                      ORDER BY u.nombre_completo ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en Usuario::read(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo usuario
     * @return bool True si el usuario se crea exitosamente, de lo contrario False
     */
    public function create() {
        try {
            // Validar datos requeridos
            if (empty(trim($this->nombre_completo)) || empty(trim($this->username)) || 
                empty(trim($this->password)) || empty($this->ID_ROL_USUARIO)) {
                error_log("Error en Usuario::create(): Datos requeridos faltantes");
                return false;
            }

            $query = "INSERT INTO " . $this->table_name . " 
                      SET nombre_completo=:nombre_completo, 
                          email=:email, 
                          notificaciones_activas=:notificaciones_activas, 
                          username=:username, 
                          password=:password, 
                          ID_ROL_USUARIO=:ID_ROL_USUARIO, 
                          id_area=:id_area, 
                          estado=:estado";

            $stmt = $this->conn->prepare($query);

            // Limpia los datos
            $this->nombre_completo = htmlspecialchars(strip_tags(trim($this->nombre_completo)));
            $this->email = !empty($this->email) ? filter_var(trim($this->email), FILTER_SANITIZE_EMAIL) : null;
            $this->notificaciones_activas = isset($this->notificaciones_activas) ? intval($this->notificaciones_activas) : 1;
            $this->username = htmlspecialchars(strip_tags(trim($this->username)));
            // Hash de la contraseña (en un sistema real, usar password_hash())
            $this->password = trim($this->password);
            $this->ID_ROL_USUARIO = intval($this->ID_ROL_USUARIO);
            $this->id_area = !empty($this->id_area) ? intval($this->id_area) : null;
            $this->estado = !empty($this->estado) ? $this->estado : 'activo';

            // Vincula los valores
            $stmt->bindParam(":nombre_completo", $this->nombre_completo);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":notificaciones_activas", $this->notificaciones_activas, PDO::PARAM_INT);
            $stmt->bindParam(":username", $this->username);
            $stmt->bindParam(":password", $this->password);
            $stmt->bindParam(":ID_ROL_USUARIO", $this->ID_ROL_USUARIO, PDO::PARAM_INT);
            $stmt->bindParam(":id_area", $this->id_area, PDO::PARAM_INT);
            $stmt->bindParam(":estado", $this->estado);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Usuario::create(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lee un único usuario
     * @return bool True si el usuario existe, de lo contrario False
     */
    public function readOne() {
        try {
            // Validar que se haya proporcionado un ID
            if (empty($this->id_usuario)) {
                error_log("Error en Usuario::readOne(): id_usuario está vacío");
                return false;
            }

            $query = "SELECT 
                        u.nombre_completo,
                        u.email,
                        u.notificaciones_activas,
                        u.username,
                        u.ID_ROL_USUARIO,
                        u.id_area,
                        u.estado,
                        r.nombre_rol,
                        a.nombre_area
                      FROM " . $this->table_name . " u
                      LEFT JOIN rol_usuario r ON u.ID_ROL_USUARIO = r.id_rol
                      LEFT JOIN areas a ON u.id_area = a.id_area
                      WHERE u.id_usuario = ? 
                      LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->nombre_completo = $row['nombre_completo'];
                $this->email = $row['email'];
                $this->notificaciones_activas = $row['notificaciones_activas'];
                $this->username = $row['username'];
                $this->ID_ROL_USUARIO = $row['ID_ROL_USUARIO'];
                $this->id_area = $row['id_area'];
                $this->estado = $row['estado'];
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en Usuario::readOne(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un usuario existente
     * @return bool True si la actualización fue exitosa, de lo contrario False
     */
    public function update() {
        try {
            // Validar datos requeridos
            if (empty($this->id_usuario) || empty(trim($this->nombre_completo)) || 
                empty(trim($this->username)) || empty($this->ID_ROL_USUARIO)) {
                error_log("Error en Usuario::update(): Datos requeridos faltantes");
                return false;
            }

            // Construir query dependiendo si se actualiza contraseña o no
            if (!empty(trim($this->password))) {
                $query = "UPDATE " . $this->table_name . " 
                          SET nombre_completo = :nombre_completo, 
                              email = :email, 
                              notificaciones_activas = :notificaciones_activas, 
                              username = :username, 
                              password = :password, 
                              ID_ROL_USUARIO = :ID_ROL_USUARIO, 
                              id_area = :id_area, 
                              estado = :estado 
                          WHERE id_usuario = :id_usuario";
            } else {
                $query = "UPDATE " . $this->table_name . " 
                          SET nombre_completo = :nombre_completo, 
                              email = :email, 
                              notificaciones_activas = :notificaciones_activas, 
                              username = :username, 
                              ID_ROL_USUARIO = :ID_ROL_USUARIO, 
                              id_area = :id_area, 
                              estado = :estado 
                          WHERE id_usuario = :id_usuario";
            }

            $stmt = $this->conn->prepare($query);

            // Limpia los datos
            $this->nombre_completo = htmlspecialchars(strip_tags(trim($this->nombre_completo)));
            $this->email = !empty($this->email) ? filter_var(trim($this->email), FILTER_SANITIZE_EMAIL) : null;
            $this->notificaciones_activas = isset($this->notificaciones_activas) ? intval($this->notificaciones_activas) : 1;
            $this->username = htmlspecialchars(strip_tags(trim($this->username)));
            $this->ID_ROL_USUARIO = intval($this->ID_ROL_USUARIO);
            $this->id_area = !empty($this->id_area) ? intval($this->id_area) : null;
            $this->estado = $this->estado ?: 'activo';
            $this->id_usuario = intval($this->id_usuario);

            // Vincula los valores
            $stmt->bindParam(':nombre_completo', $this->nombre_completo);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':notificaciones_activas', $this->notificaciones_activas, PDO::PARAM_INT);
            $stmt->bindParam(':username', $this->username);
            $stmt->bindParam(':ID_ROL_USUARIO', $this->ID_ROL_USUARIO, PDO::PARAM_INT);
            $stmt->bindParam(':id_area', $this->id_area, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $this->estado);
            $stmt->bindParam(':id_usuario', $this->id_usuario, PDO::PARAM_INT);

            // Vincular contraseña solo si se proporciona
            if (!empty(trim($this->password))) {
                $this->password = trim($this->password);
                $stmt->bindParam(':password', $this->password);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Usuario::update(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un usuario
     * @return bool True si la eliminación fue exitosa, de lo contrario False
     */
    public function delete() {
        try {
            // Validar que se haya proporcionado un ID
            if (empty($this->id_usuario)) {
                error_log("Error en Usuario::delete(): id_usuario está vacío");
                return false;
            }

            $query = "DELETE FROM " . $this->table_name . " WHERE id_usuario = ?";

            $stmt = $this->conn->prepare($query);

            // Limpia el ID y asegura que sea un entero
            $this->id_usuario = intval($this->id_usuario);

            // Vincula el ID
            $stmt->bindParam(1, $this->id_usuario, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Usuario::delete(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si existe un usuario con el username dado
     * @param string $username Username a verificar
     * @param int|null $exclude_id ID a excluir de la búsqueda (útil para updates)
     * @return bool True si existe, False si no existe
     */
    public function existeUsername($username, $exclude_id = null) {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name . " 
                      WHERE username = ?";
            $params = [trim($username)];

            if ($exclude_id) {
                $query .= " AND id_usuario != ?";
                $params[] = intval($exclude_id);
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en Usuario::existeUsername(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el total de usuarios
     * @return int Número total de usuarios
     */
    public function count() {
        try {
            $query = "SELECT COUNT(*) FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en Usuario::count(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene todos los roles para llenar selectores
     * @return PDOStatement|false
     */
    public function getRoles() {
        try {
            $query = "SELECT id_rol, nombre_rol FROM rol_usuario ORDER BY nombre_rol ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en Usuario::getRoles(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todas las áreas para llenar selectores
     * @return PDOStatement|false
     */
    public function getAreas() {
        try {
            $query = "SELECT 
                        a.id_area, 
                        a.nombre_area, 
                        s.nombre_sede,
                        CONCAT(a.nombre_area, ' - ', s.nombre_sede) as area_completa
                      FROM areas a
                      LEFT JOIN sedes s ON a.id_sede = s.id_sede
                      ORDER BY s.nombre_sede ASC, a.nombre_area ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en Usuario::getAreas(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene estadísticas de usuarios por rol
     * @return array Array con estadísticas
     */
    public function getStatsPerRole() {
        try {
            $query = "SELECT 
                        r.nombre_rol,
                        COUNT(u.id_usuario) as total_usuarios,
                        SUM(CASE WHEN u.estado = 'activo' THEN 1 ELSE 0 END) as usuarios_activos,
                        SUM(CASE WHEN u.estado = 'inactivo' THEN 1 ELSE 0 END) as usuarios_inactivos
                      FROM rol_usuario r
                      LEFT JOIN usuarios u ON r.id_rol = u.ID_ROL_USUARIO
                      GROUP BY r.id_rol, r.nombre_rol
                      ORDER BY r.nombre_rol ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Usuario::getStatsPerRole(): " . $e->getMessage());
            return array();
        }
    }

    /**
     * Autentica un usuario
     * @param string $username Username del usuario
     * @param string $password Contraseña del usuario
     * @return array|false Array con datos del usuario si es exitoso, false en caso contrario
     */
    public function authenticate($username, $password) {
        try {
            $query = "SELECT 
                        u.id_usuario,
                        u.nombre_completo,
                        u.username,
                        u.password,
                        u.ID_ROL_USUARIO,
                        u.id_area,
                        u.estado,
                        r.nombre_rol,
                        a.nombre_area
                      FROM " . $this->table_name . " u
                      LEFT JOIN rol_usuario r ON u.ID_ROL_USUARIO = r.id_rol
                      LEFT JOIN areas a ON u.id_area = a.id_area
                      WHERE u.username = ? AND u.estado = 'activo'
                      LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $username);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && $row['password'] === $password) {
                // Remover la contraseña del array antes de devolverlo
                unset($row['password']);
                return $row;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Error en Usuario::authenticate(): " . $e->getMessage());
            return false;
        }
    }
}
?>