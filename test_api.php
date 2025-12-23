<?php
// Simulate GET request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['action'] = 'by_user';
$_GET['id_usuario'] = '3';

// Capture output
ob_start();
chdir('api/controllers');
require 'incidencias.php';
$output = ob_get_clean();

echo $output;
?>
