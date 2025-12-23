<?php
/**
 * Script de Diagnóstico del Sistema de Notificaciones
 * Ejecutar: php verificar_notificaciones.php
 */

require_once '../api/models/database.php';

echo "=== DIAGNÓSTICO DEL SISTEMA DE NOTIFICACIONES ===\n\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        die("❌ Error: No se pudo conectar a la base de datos\n");
    }
    
    echo "✅ Conexión a base de datos exitosa\n\n";
    
    // 1. Verificar tabla de notificaciones
    echo "--- 1. VERIFICANDO TABLA DE NOTIFICACIONES ---\n";
    $stmt = $db->query("SHOW TABLES LIKE 'notificaciones'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabla 'notificaciones' existe\n";
        
        // Mostrar estructura
        $stmt = $db->query("DESCRIBE notificaciones");
        echo "\nEstructura de la tabla:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - {$row['Field']} ({$row['Type']})\n";
        }
    } else {
        echo "❌ Tabla 'notificaciones' NO existe\n";
    }
    
    // 2. Contar notificaciones
    echo "\n--- 2. ESTADÍSTICAS DE NOTIFICACIONES ---\n";
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN leida = 0 THEN 1 ELSE 0 END) as no_leidas,
            SUM(CASE WHEN leida = 1 THEN 1 ELSE 0 END) as leidas
        FROM notificaciones
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de notificaciones: {$stats['total']}\n";
    echo "No leídas: {$stats['no_leidas']}\n";
    echo "Leídas: {$stats['leidas']}\n";
    
    // 3. Últimas notificaciones
    echo "\n--- 3. ÚLTIMAS 5 NOTIFICACIONES ---\n";
    $stmt = $db->query("
        SELECT 
            n.id_notificacion,
            n.id_incidencia,
            n.tipo_notificacion,
            n.asunto,
            n.leida,
            n.fecha_envio,
            u.nombre_completo as destinatario,
            u.username
        FROM notificaciones n
        LEFT JOIN usuarios u ON n.id_usuario_destino = u.id_usuario
        ORDER BY n.fecha_envio DESC
        LIMIT 5
    ");
    
    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $leida = $row['leida'] ? '✓ Leída' : '✗ No leída';
            echo "\n  ID: {$row['id_notificacion']}\n";
            echo "  Incidencia: #{$row['id_incidencia']}\n";
            echo "  Tipo: {$row['tipo_notificacion']}\n";
            echo "  Destinatario: {$row['destinatario']} ({$row['username']})\n";
            echo "  Asunto: {$row['asunto']}\n";
            echo "  Estado: {$leida}\n";
            echo "  Fecha: {$row['fecha_envio']}\n";
            echo "  " . str_repeat("-", 50) . "\n";
        }
    } else {
        echo "  No hay notificaciones en el sistema\n";
    }
    
    // 4. Verificar incidencias en estado "en_verificacion"
    echo "\n--- 4. INCIDENCIAS EN VERIFICACIÓN ---\n";
    $stmt = $db->query("
        SELECT 
            i.id_incidencia,
            i.titulo,
            i.estado,
            i.id_usuario_reporta,
            u.nombre_completo as reportante,
            u.username
        FROM incidencias i
        LEFT JOIN usuarios u ON i.id_usuario_reporta = u.id_usuario
        WHERE i.estado = 'en_verificacion'
        ORDER BY i.fecha_reporte DESC
    ");
    
    if ($stmt->rowCount() > 0) {
        echo "Total: {$stmt->rowCount()} incidencias\n\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  #{$row['id_incidencia']} - {$row['titulo']}\n";
            echo "  Reportante: {$row['reportante']} ({$row['username']})\n";
            
            // Verificar si tiene notificación
            $stmtNotif = $db->prepare("
                SELECT COUNT(*) as count 
                FROM notificaciones 
                WHERE id_incidencia = ? AND tipo_notificacion = 'cambio_estado'
            ");
            $stmtNotif->execute([$row['id_incidencia']]);
            $notifCount = $stmtNotif->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($notifCount > 0) {
                echo "  ✅ Tiene {$notifCount} notificación(es)\n";
            } else {
                echo "  ❌ NO tiene notificaciones\n";
            }
            echo "  " . str_repeat("-", 50) . "\n";
        }
    } else {
        echo "  No hay incidencias en estado 'en_verificacion'\n";
    }
    
    // 5. Verificar archivo EmailNotifier.php
    echo "\n--- 5. VERIFICANDO ARCHIVOS DEL SISTEMA ---\n";
    $files = [
        '../api/models/EmailNotifier.php' => 'EmailNotifier',
        '../api/controllers/notificaciones.php' => 'Controlador de Notificaciones',
        '../api/controllers/incidencias.php' => 'Controlador de Incidencias'
    ];
    
    foreach ($files as $file => $name) {
        if (file_exists($file)) {
            echo "✅ {$name}: Existe\n";
        } else {
            echo "❌ {$name}: NO EXISTE\n";
        }
    }
    
    // 6. Verificar que el código de notificaciones está en incidencias.php
    echo "\n--- 6. VERIFICANDO INTEGRACIÓN EN INCIDENCIAS.PHP ---\n";
    $incidenciasContent = file_get_contents('../api/controllers/incidencias.php');
    
    if (strpos($incidenciasContent, 'notificarCambioEstado') !== false) {
        echo "✅ Método 'notificarCambioEstado' encontrado\n";
    } else {
        echo "❌ Método 'notificarCambioEstado' NO encontrado\n";
    }
    
    if (strpos($incidenciasContent, 'estadoAnterior') !== false) {
        echo "✅ Variable 'estadoAnterior' encontrada (captura estado previo)\n";
    } else {
        echo "❌ Variable 'estadoAnterior' NO encontrada\n";
    }
    
    if (strpos($incidenciasContent, 'AppEmailNotifier') !== false) {
        echo "✅ Clase 'AppEmailNotifier' encontrada\n";
    } else {
        echo "❌ Clase 'AppEmailNotifier' NO encontrada\n";
    }
    
    echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
    echo "\nSi todo está ✅, el sistema debería funcionar correctamente.\n";
    echo "Si hay ❌, revisa los archivos correspondientes.\n\n";
    
} catch (Exception $e) {
    echo "❌ Error durante el diagnóstico: " . $e->getMessage() . "\n";
}
?>
