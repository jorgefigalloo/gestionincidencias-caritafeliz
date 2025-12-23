<?php
// Script de prueba para verificar el email de la incidencia #19
require_once 'api/models/database.php';

$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare("
    SELECT i.*, 
           u.email as usuario_email,
           i.email_reporta,
           COALESCE(i.email_reporta, u.email) as email_usuario,
           COALESCE(i.nombre_reporta, u.nombre_completo) as nombre_usuario
    FROM incidencias i 
    LEFT JOIN usuarios u ON i.id_usuario_reporta = u.id_usuario 
    WHERE i.id_incidencia = 19
");
$stmt->execute();
$incidencia = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h2>Información de Incidencia #19</h2>";
echo "<pre>";
echo "ID Incidencia: " . $incidencia['id_incidencia'] . "\n";
echo "Título: " . $incidencia['titulo'] . "\n";
echo "ID Usuario Reporta: " . ($incidencia['id_usuario_reporta'] ?? 'NULL') . "\n";
echo "Email Reporta (campo directo): " . ($incidencia['email_reporta'] ?? 'NULL') . "\n";
echo "Usuario Email (de tabla usuarios): " . ($incidencia['usuario_email'] ?? 'NULL') . "\n";
echo "Email Usuario (COALESCE): " . ($incidencia['email_usuario'] ?? 'NULL') . "\n";
echo "Nombre Usuario (COALESCE): " . ($incidencia['nombre_usuario'] ?? 'NULL') . "\n";
echo "</pre>";

// Verificar también el usuario
if ($incidencia['id_usuario_reporta']) {
    $stmt2 = $db->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
    $stmt2->execute([$incidencia['id_usuario_reporta']]);
    $usuario = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Información del Usuario</h2>";
    echo "<pre>";
    print_r($usuario);
    echo "</pre>";
}
?>
