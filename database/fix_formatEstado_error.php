<?php
// Script para arreglar el error de formatEstado en respuesta.php

$file = 'c:/xampp/htdocs/gestion-incidencias/views/respuesta.php';
$content = file_get_contents($file);

// Buscar la línea problemática con formatEstado
$search = "Estado actual: \${formatEstado(incidencia.estado)}";
$replace = "Estado actual: \${incidencia.estado.toUpperCase().replace('_', ' ')}";

$newContent = str_replace($search, $replace, $content);

if ($newContent !== $content) {
    file_put_contents($file, $newContent);
    echo "✅ Error de formatEstado corregido\n";
    echo "Ahora usa: incidencia.estado.toUpperCase().replace('_', ' ')\n";
} else {
    echo "❌ No se encontró la línea a reemplazar\n";
    echo "Buscando alternativas...\n";
    
    // Intentar buscar con escape de caracteres
    $search2 = "Estado actual: \\\${formatEstado(incidencia.estado)}";
    $replace2 = "Estado actual: \\\${incidencia.estado.toUpperCase().replace('_', ' ')}";
    
    $newContent = str_replace($search2, $replace2, $content);
    
    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        echo "✅ Error corregido (alternativa)\n";
    } else {
        echo "❌ No se pudo corregir automáticamente\n";
    }
}
?>
