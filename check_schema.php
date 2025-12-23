<?php
require_once 'api/models/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Checking tables...\n";
    
    $tables = ['incidencias', 'subtipos_incidencias'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "Table '$table' exists.\n";
            
            $stmt = $db->query("DESCRIBE $table");
            echo "Columns in '$table':\n";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo " - " . $row['Field'] . "\n";
            }
        } else {
            echo "Table '$table' DOES NOT exist.\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
