<?php
require_once 'connection.php';

$conn = ConexionDB::setConnection();

echo "<h1>Prueba de Búsqueda de Productos</h1>";

// 1. Listar TODOS los productos
echo "<h2>1. Todos los productos:</h2>";
$sql1 = "SELECT * FROM producto LIMIT 10";
$stmt1 = $conn->prepare($sql1);
$stmt1->execute();
$productos1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($productos1);
echo "</pre>";

// 2. Productos con stock > 0
echo "<h2>2. Productos con stock > 0:</h2>";
$sql2 = "SELECT * FROM producto WHERE stock > 0 LIMIT 10";
$stmt2 = $conn->prepare($sql2);
$stmt2->execute();
$productos2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($productos2);
echo "</pre>";

// 3. Buscar producto específico (cambia P001 por tu código)
echo "<h2>3. Buscar 'P001':</h2>";
$sql3 = "SELECT 
            p.idProducto,
            p.stock,
            p.kilataje,
            p.descripcion,
            p.precioUnitario,
            p.gramos,
            c.nombre as categoria
        FROM producto p
        INNER JOIN categoria c ON p.idCategoria = c.idCategoria
        WHERE p.idProducto LIKE '%P001%'
        AND p.stock > 0";
$stmt3 = $conn->prepare($sql3);
$stmt3->execute();
$productos3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($productos3);
echo "</pre>";

// 4. Verificar tabla categoria
echo "<h2>4. Categorías disponibles:</h2>";
$sql4 = "SELECT * FROM categoria";
$stmt4 = $conn->prepare($sql4);
$stmt4->execute();
$categorias = $stmt4->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($categorias);
echo "</pre>";
?>