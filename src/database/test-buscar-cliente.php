<?php
require_once 'connection.php';

$conn = ConexionDB::setConnection();

// Listar TODOS los clientes mayoristas
$sql = "SELECT * FROM cliente WHERE idTipoCliente = 2";
$stmt = $conn->prepare($sql);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h1>Clientes Mayoristas en la BD:</h1>";
echo "<pre>";
print_r($clientes);
echo "</pre>";
?>