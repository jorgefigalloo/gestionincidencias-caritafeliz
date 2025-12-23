<?php
/**
 * Script de Migración - Agregar Campos de Email y Notificaciones
 * ================================================================
 * 
 * Este script agrega los campos 'email' y 'notificaciones_activas' 
 * a la tabla 'usuarios' en el hosting de InfinityFree.
 * 
 * INSTRUCCIONES:
 * 1. Subir este archivo a: /htdocs/database/add_email_fields.php
 * 2. Acceder desde el navegador: https://tu-dominio.com/database/add_email_fields.php
 * 3. Verificar el mensaje de éxito
 * 4. ELIMINAR este archivo por seguridad
 */

header('Content-Type: text/html; charset=utf-8');

// Incluir configuración de base de datos
require_once '../api/config/database.php';

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='utf-8'><title>Migración de Base de Datos</title>";
echo "<style>body{font-family:Arial;padding:20px;max-width:800px;margin:0 auto;}";
echo ".success{color:green;background:#e8f5e9;padding:10px;border-radius:5px;margin:10px 0;}";
echo ".error{color:red;background:#ffebee;padding:10px;border-radius:5px;margin:10px 0;}";
echo ".info{color:blue;background:#e3f2fd;padding:10px;border-radius:5px;margin:10px 0;}";
echo ".warning{color:orange;background:#fff3e0;padding:10px;border-radius:5px;margin:10px 0;}";
echo "</style></head><body>";

echo "<h1>🔧 Migración de Base de Datos - Email y Notificaciones</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

try {
    // Conectar a la base de datos
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<div class='info'>✓ Conexión a base de datos establecida</div>";
    
    // Verificar si el campo 'email' ya existe
    $checkQuery = "SHOW COLUMNS FROM usuarios LIKE 'email'";
    $stmt = $db->prepare($checkQuery);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "<div class='warning'>⚠️ Los campos ya existen en la tabla 'usuarios'</div>";
        echo "<p>No es necesario ejecutar la migración.</p>";
        
        // Mostrar estructura actual
        $showQuery = "DESCRIBE usuarios";
        $stmt = $db->prepare($showQuery);
        $stmt->execute();
        
        echo "<h3>Estructura actual de la tabla 'usuarios':</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<div class='info'>📝 Iniciando migración...</div>";
        
        // Ejecutar migración
        $alterQuery = "ALTER TABLE usuarios 
                      ADD COLUMN email VARCHAR(100) NULL AFTER username,
                      ADD COLUMN notificaciones_activas TINYINT(1) DEFAULT 1 AFTER email";
        
        $db->exec($alterQuery);
        
        echo "<div class='success'>✅ ¡Migración completada exitosamente!</div>";
        echo "<p>Se han agregado los siguientes campos a la tabla 'usuarios':</p>";
        echo "<ul>";
        echo "<li><strong>email</strong>: VARCHAR(100) NULL - Email del usuario (opcional)</li>";
        echo "<li><strong>notificaciones_activas</strong>: TINYINT(1) DEFAULT 1 - Estado de notificaciones</li>";
        echo "</ul>";
        
        // Verificar campos agregados
        $verifyQuery = "DESCRIBE usuarios";
        $stmt = $db->prepare($verifyQuery);
        $stmt->execute();
        
        echo "<h3>Estructura actualizada de la tabla 'usuarios':</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $highlight = ($row['Field'] == 'email' || $row['Field'] == 'notificaciones_activas') 
                        ? "style='background-color:#c8e6c9;'" : "";
            echo "<tr $highlight>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div class='warning'>⚠️ IMPORTANTE: Por seguridad, elimina este archivo después de ejecutar la migración.</div>";
    }
    
    // Mostrar información de usuarios actuales
    $usersQuery = "SELECT id_usuario, nombre_completo, username, email, notificaciones_activas FROM usuarios LIMIT 5";
    $stmt = $db->prepare($usersQuery);
    $stmt->execute();
    
    echo "<h3>Usuarios actuales (primeros 5):</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Username</th><th>Email</th><th>Notificaciones</th></tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id_usuario']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nombre_completo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['notificaciones_activas']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(PDOException $e) {
    echo "<div class='error'>❌ Error en la migración:</div>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p>Verifica:</p>";
    echo "<ul>";
    echo "<li>Las credenciales de la base de datos en <code>api/config/database.php</code></li>";
    echo "<li>Los permisos del usuario de la base de datos</li>";
    echo "<li>Que la tabla 'usuarios' exista</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><small>Script de migración para Sistema de Gestión de Incidencias</small></p>";
echo "</body></html>";
?>
