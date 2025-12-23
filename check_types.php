<?php
require_once 'api/models/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT * FROM tipos_incidencias";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Types found: " . count($types) . "\n";
    foreach ($types as $type) {
        echo "- " . $type['nombre'] . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
