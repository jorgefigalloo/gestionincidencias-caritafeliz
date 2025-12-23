<?php
// Script para eliminar BOM de archivos PHP

function removeBOM($file) {
    if (!file_exists($file)) {
        echo "❌ Archivo no encontrado: $file\n";
        return false;
    }
    
    $content = file_get_contents($file);
    
    // Detectar y eliminar BOM UTF-8 (EF BB BF)
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = substr($content, 3);
        file_put_contents($file, $content);
        echo "✅ BOM eliminado de: $file\n";
        return true;
    } else {
        echo "ℹ️  Sin BOM: $file\n";
        return false;
    }
}

echo "=== Eliminando BOM de archivos PHP ===\n\n";

$files = [
    __DIR__ . '/../api/controllers/incidencias.php',
    __DIR__ . '/../api/models/IncidenciaEmailNotifier.php',
    __DIR__ . '/../api/models/SubtipoIncidencia.php',
    __DIR__ . '/../api/models/EmailNotifier.php',
    __DIR__ . '/../includes/email_notifier.php',
    __DIR__ . '/../api/models/database.php',
    __DIR__ . '/../api/models/Incidencia.php',
    __DIR__ . '/../api/models/Usuario.php',
    __DIR__ . '/../api/models/TipoIncidencia.php'
];

$removed = 0;
foreach ($files as $file) {
    if (removeBOM($file)) {
        $removed++;
    }
}

echo "\n=== Resumen ===\n";
echo "Total de archivos procesados: " . count($files) . "\n";
echo "BOM eliminados: $removed\n";
echo "\n✅ Proceso completado. Por favor, recarga la página y prueba de nuevo.\n";
