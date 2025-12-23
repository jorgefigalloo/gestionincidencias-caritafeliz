<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

echo json_encode([
    'success' => true,
    'message' => 'API funcionando',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>