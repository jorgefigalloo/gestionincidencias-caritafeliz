<?php
// tests/verify_confirmation.php

$baseUrl = 'http://localhost/gestion-incidencias/api/controllers/incidencias.php';

// 1. Test Connectivity
echo "Testing connectivity to $baseUrl...\n";
$headers = @get_headers($baseUrl);
if ($headers && strpos($headers[0], '200') !== false) {
    echo "✅ API is reachable.\n";
} else {
    echo "❌ API is NOT reachable. Headers: " . print_r($headers, true) . "\n";
    // Try with a different path if needed, or exit
}

// 2. Test Confirmation
echo "\nTesting confirmation action...\n";
$url = $baseUrl . '?action=confirmar_solucion';
$data = array(
    'id_incidencia' => 19, 
    'confirmacion' => 'solucionado',
    'comentario_usuario' => 'Verificación automática del fix'
);

$options = array(
    'http' => array(
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true // Fetch content even on failure status codes
    )
);

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "Respuesta del servidor:\n";
echo $result . "\n";

$json = json_decode($result, true);
if (isset($json['success']) && $json['success'] === true) {
    echo "✅ Prueba exitosa: La confirmación fue procesada correctamente.\n";
} else {
    echo "❌ Prueba fallida: " . ($json['message'] ?? 'Error desconocido') . "\n";
}
?>
