<?php
// Clase para gestionar la conexión a la base de datos
class Database {
    // Credenciales de la base de datos
    private $host = "localhost";
    private $db_name = "gestion_ti_clarita";
    private $username =  "root";
    private $password =  "";
    public $conn;

    /**
     * Obtiene la conexión a la base de datos
     * @return PDO|null La conexión si es exitosa, o null si falla
     */
    public function getConnection(){
        $this->conn = null;
        try {
            // Se crea una nueva instancia de PDO para la conexión
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            
            // Se configura el manejo de errores para que PDO lance excepciones
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Se establece el juego de caracteres a UTF8 para evitar problemas de codificación
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception){
            // En caso de error, registra el error en el log del servidor
            // En lugar de hacer echo (que interfiere con las respuestas JSON)
            error_log("Error de conexión a la base de datos: " . $exception->getMessage());
            return null;
        }
        return $this->conn;
    }
}
?>