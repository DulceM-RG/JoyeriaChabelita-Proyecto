<?php
// test-insert.php - Script de prueba
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

echo json_encode([
    'test' => 'Servidor funcionando',
    'php_version' => phpversion(),
    'archivo' => __FILE__
]);
?>