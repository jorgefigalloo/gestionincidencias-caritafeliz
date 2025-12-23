<?php
// Script para probar manualmente la creación de notificaciones

require_once __DIR__ . '/../api/models/database.php';
require_once __DIR__ . '/../api/models/EmailNotifier.php';

echo "=== PRUEBA MANUAL DE NOTIFICACIONES ===\n\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Crear instancia del notificador
    $notifier = new AppEmailNotifier($db);
    
    // Obtener admin
    $stmt = $db->prepare("
        SELECT u.id_usuario 
        FROM usuarios u 
        INNER JOIN rol_usuario r ON u.ID_ROL_USUARIO = r.id_rol 
        WHERE r.nombre_rol = 'admin' AND u.estado = 'activo'
        LIMIT 1
    ");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        die("❌ No hay administradores activos\n");
    }
    
    echo "✅ Admin encontrado: ID {$admin['id_usuario']}\n";
    
    // Probar notificación para incidencia #17
    echo "\n--- Probando notificarNuevaIncidencia() ---\n";
    $resultado = $notifier->notificarNuevaIncidencia(17, $admin['id_usuario']);
    
    if ($resultado) {
        echo "✅ Notificación creada exitosamente\n";
    } else {
        echo "❌ Error al crear notificación\n";
    }
    
    // Verificar en BD
    echo "\n--- Verificando en base de datos ---\n";
    $stmt = $db->query("SELECT * FROM notificaciones WHERE id_incidencia = 17 ORDER BY fecha_envio DESC LIMIT 1");
    if ($stmt->rowCount() > 0) {
        $notif = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Notificación encontrada:\n";
        echo "  ID: {$notif['id_notificacion']}\n";
        echo "  Tipo: {$notif['tipo_notificacion']}\n";
        echo "  Asunto: {$notif['asunto']}\n";
        echo "  Usuario: {$notif['id_usuario_destino']}\n";
    } else {
        echo "❌ No se encontró la notificación en la BD\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
