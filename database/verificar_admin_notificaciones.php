<?php
// Script para verificar administradores y notificaciones

require_once __DIR__ . '/../api/models/database.php';

echo "=== VERIFICACIÓN DE NOTIFICACIONES ===\n\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // 1. Verificar administradores
    echo "--- 1. ADMINISTRADORES EN EL SISTEMA ---\n";
    $stmt = $db->prepare("
        SELECT u.id_usuario, u.nombre_completo, u.username, r.nombre_rol, u.estado 
        FROM usuarios u 
        INNER JOIN rol_usuario r ON u.ID_ROL_USUARIO = r.id_rol 
        WHERE r.nombre_rol = 'admin'
    ");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "ID: {$row['id_usuario']} | {$row['nombre_completo']} ({$row['username']}) | Estado: {$row['estado']}\n";
        }
    } else {
        echo "❌ NO HAY ADMINISTRADORES EN EL SISTEMA\n";
    }
    
    // 2. Verificar notificaciones de la incidencia #17
    echo "\n--- 2. NOTIFICACIONES DE INCIDENCIA #17 ---\n";
    $stmt = $db->query("SELECT * FROM notificaciones WHERE id_incidencia = 17 ORDER BY fecha_envio DESC");
    
    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "ID: {$row['id_notificacion']} | Tipo: {$row['tipo_notificacion']} | Usuario: {$row['id_usuario_destino']} | Fecha: {$row['fecha_envio']}\n";
        }
    } else {
        echo "❌ NO HAY NOTIFICACIONES PARA LA INCIDENCIA #17\n";
    }
    
    // 3. Verificar última incidencia creada
    echo "\n--- 3. ÚLTIMA INCIDENCIA CREADA ---\n";
    $stmt = $db->query("SELECT * FROM incidencias ORDER BY id_incidencia DESC LIMIT 1");
    $incidencia = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "ID: {$incidencia['id_incidencia']} | Título: {$incidencia['titulo']} | Prioridad: {$incidencia['prioridad']}\n";
    
    // 4. Verificar si el código de notificación se ejecutó
    echo "\n--- 4. VERIFICANDO LOGS DE PHP ---\n";
    $logFile = 'C:/xampp/apache/logs/error.log';
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        if (strpos($logs, 'Error al notificar a administradores') !== false) {
            echo "❌ HAY ERRORES AL NOTIFICAR ADMINISTRADORES\n";
        } else {
            echo "✅ No hay errores de notificación en los logs\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
