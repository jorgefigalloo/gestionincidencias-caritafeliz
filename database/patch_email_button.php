<?php
// Script para mejorar el botón de email en respuesta.php

$file = 'c:/xampp/htdocs/gestion-incidencias/views/respuesta.php';
$content = file_get_contents($file);

// Buscar el botón de email actual
$search = "                                \${incidencia.email_reporta ? `\r\n                                    <a href=\"mailto:\${incidencia.email_reporta}\" \r\n                                        class=\"flex-1 lg:flex-none bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-xl text-sm font-medium transition-colors flex items-center justify-center\">\r\n                                        <i class=\"fas fa-envelope mr-2 text-gray-400\"></i> Email\r\n                                    </a>\r\n                                ` : ''}";

$replace = "                                \${incidencia.email_reporta ? `\r\n                                    <a href=\"mailto:\${incidencia.email_reporta}?subject=\${encodeURIComponent(`Actualización de tu Incidencia #\${incidencia.id_incidencia} - \${incidencia.titulo}`)}&body=\${encodeURIComponent(`Hola \${incidencia.nombre_reporta},\\n\\nTe escribo en relación a tu incidencia:\\n\\nID: #\${incidencia.id_incidencia}\\nTítulo: \${incidencia.titulo}\\nEstado actual: \${formatEstado(incidencia.estado)}\\nPrioridad: \${incidencia.prioridad.toUpperCase()}\\n\\n[Escribe aquí tu mensaje]\\n\\nSaludos,\\n\${currentUser.nombre_completo || currentUser.username}\\nSoporte Técnico - Clínica Carita Feliz`)}\" \r\n                                        class=\"flex-1 lg:flex-none bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-xl text-sm font-medium transition-colors flex items-center justify-center\"\r\n                                        title=\"Enviar email a \${incidencia.nombre_reporta}\">\r\n                                        <i class=\"fas fa-envelope mr-2 text-gray-400\"></i> Email\r\n                                    </a>\r\n                                ` : ''}";

$newContent = str_replace($search, $replace, $content);

if ($newContent !== $content) {
    file_put_contents($file, $newContent);
    echo "✅ Botón de email mejorado exitosamente\n";
    echo "Ahora incluye:\n";
    echo "  - Asunto: Actualización de tu Incidencia #X - Título\n";
    echo "  - Cuerpo: Información completa de la incidencia\n";
    echo "  - Firma del técnico\n";
} else {
    echo "❌ No se pudo realizar el reemplazo\n";
}
?>
