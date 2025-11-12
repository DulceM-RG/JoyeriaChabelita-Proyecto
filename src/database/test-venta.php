<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once 'connection.php';

echo "<h1>🧪 Test de Sistema de Ventas</h1>";

// 1. Verificar sesión
echo "<h2>1. Verificar Sesión</h2>";
if (isset($_SESSION['usuario'])) {
    echo "<pre style='background:#e8f5e9;padding:10px;'>";
    echo "✅ HAY SESIÓN ACTIVA\n";
    print_r($_SESSION['usuario']);
    echo "</pre>";
} else {
    echo "<pre style='background:#ffebee;padding:10px;'>";
    echo "❌ NO HAY SESIÓN ACTIVA\n";
    echo "Inicia sesión primero en login.html";
    echo "</pre>";
    exit;
}

$conn = ConexionDB::setConnection();

// 2. Ver productos disponibles
echo "<h2>2. Productos Disponibles</h2>";
$sqlProductos = "SELECT p.idProducto, p.descripcion, p.stock, p.precioUnitario, c.nombre as categoria 
                 FROM producto p 
                 INNER JOIN categoria c ON p.idCategoria = c.idCategoria 
                 WHERE p.stock > 0 
                 LIMIT 5";
$stmtProductos = $conn->prepare($sqlProductos);
$stmtProductos->execute();
$productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Código</th><th>Descripción</th><th>Stock</th><th>Precio</th></tr>";
foreach ($productos as $p) {
    echo "<tr>";
    echo "<td>{$p['idProducto']}</td>";
    echo "<td>{$p['descripcion']}</td>";
    echo "<td>{$p['stock']}</td>";
    echo "<td>\${$p['precioUnitario']}</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Ver clientes mayoristas
echo "<h2>3. Clientes Mayoristas</h2>";
$sqlClientes = "SELECT * FROM cliente WHERE idTipoCliente = 2 LIMIT 5";
$stmtClientes = $conn->prepare($sqlClientes);
$stmtClientes->execute();
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nombre</th><th>Apellidos</th><th>Teléfono</th></tr>";
foreach ($clientes as $c) {
    echo "<tr>";
    echo "<td>{$c['idCliente']}</td>";
    echo "<td>{$c['nombre']}</td>";
    echo "<td>{$c['apellidoPaterno']} {$c['apellidoMaterno']}</td>";
    echo "<td>{$c['telefono']}</td>";
    echo "</tr>";
}
echo "</table>";

// 4. Simular una venta
echo "<h2>4. Simular Venta</h2>";
echo "<form method='POST' action='test-guardar-venta.php'>";
echo "<p>Cliente: <select name='idCliente'>";
echo "<option value='1'>Público General</option>";
foreach ($clientes as $c) {
    $nombreCompleto = $c['nombre'] . ' ' . $c['apellidoPaterno'] . ' ' . $c['apellidoMaterno'];
    echo "<option value='{$c['idCliente']}'>{$nombreCompleto}</option>";
}
echo "</select></p>";

echo "<p>Producto: <select name='idProducto'>";
foreach ($productos as $p) {
    echo "<option value='{$p['idProducto']}'>{$p['idProducto']} - {$p['descripcion']} (\${$p['precioUnitario']})</option>";
}
echo "</select></p>";

echo "<p>Cantidad: <input type='number' name='cantidad' value='1' min='1'></p>";
echo "<p>Método de pago: <select name='metodoPago'><option>efectivo</option><option>tarjeta</option></select></p>";
echo "<button type='submit'>Procesar Venta de Prueba</button>";
echo "</form>";

// 5. Ver últimas ventas
echo "<h2>5. Últimas 5 Ventas</h2>";
$sqlVentas = "SELECT v.*, c.nombre as nombreCliente, c.apellidoPaterno, e.nombre as nombreEmpleado
              FROM venta v
              INNER JOIN cliente c ON v.idCliente = c.idCliente
              INNER JOIN empleado e ON v.idEmpleado = e.idEmpleado
              ORDER BY v.idVenta DESC
              LIMIT 5";
$stmtVentas = $conn->prepare($sqlVentas);
$stmtVentas->execute();
$ventas = $stmtVentas->fetchAll(PDO::FETCH_ASSOC);

if (count($ventas) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Fecha</th><th>Cliente</th><th>Empleado</th></tr>";
    foreach ($ventas as $v) {
        echo "<tr>";
        echo "<td>{$v['idVenta']}</td>";
        echo "<td>{$v['fechaVenta']}</td>";
        echo "<td>{$v['nombreCliente']} {$v['apellidoPaterno']}</td>";
        echo "<td>{$v['nombreEmpleado']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>❌ No hay ventas registradas</p>";
}

echo "<hr>";
echo "<p><a href='test-guardar-venta-directo.php'>🧪 Test Directo de Guardar Venta (sin formulario)</a></p>";
?>