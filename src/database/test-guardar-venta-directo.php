<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once 'connection.php';

echo "<h1>🧪 Test Directo de Guardar Venta</h1>";

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    echo "<p style='color:red;'>❌ No hay sesión. <a href='../login.html'>Inicia sesión</a></p>";
    exit;
}

try {
    $conn = ConexionDB::setConnection();
    $conn->beginTransaction();
    
    $idEmpleado = $_SESSION['usuario']['idEmpleado'];
    $idCliente = 1; // Público
    $fechaVenta = date('Y-m-d');
    
    echo "<h2>📝 Datos de la venta:</h2>";
    echo "<ul>";
    echo "<li>Empleado ID: $idEmpleado</li>";
    echo "<li>Cliente ID: $idCliente</li>";
    echo "<li>Fecha: $fechaVenta</li>";
    echo "</ul>";
    
    // Buscar un producto con stock
    $sqlProd = "SELECT idProducto, stock, precioUnitario FROM producto WHERE stock > 0 LIMIT 1";
    $stmtProd = $conn->prepare($sqlProd);
    $stmtProd->execute();
    $producto = $stmtProd->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        echo "<p style='color:red;'>❌ No hay productos con stock</p>";
        exit;
    }
    
    echo "<h2>🛍️ Producto a vender:</h2>";
    echo "<ul>";
    echo "<li>ID: {$producto['idProducto']}</li>";
    echo "<li>Stock actual: {$producto['stock']}</li>";
    echo "<li>Precio: \${$producto['precioUnitario']}</li>";
    echo "</ul>";
    
    $cantidad = 1;
    $total = $producto['precioUnitario'] * $cantidad;
    
    // 1. Crear/actualizar ingreso
    echo "<h2>💰 Paso 1: Ingreso</h2>";
    $sqlCheckIngreso = "SELECT idIngreso, importeTotal FROM ingreso WHERE fecha = :fecha";
    $stmtCheckIngreso = $conn->prepare($sqlCheckIngreso);
    $stmtCheckIngreso->execute([':fecha' => $fechaVenta]);
    $ingreso = $stmtCheckIngreso->fetch(PDO::FETCH_ASSOC);
    
    if ($ingreso) {
        $idIngreso = $ingreso['idIngreso'];
        $nuevoTotal = $ingreso['importeTotal'] + $total;
        $sqlUpdateIngreso = "UPDATE ingreso SET importeTotal = :total WHERE idIngreso = :id";
        $stmtUpdateIngreso = $conn->prepare($sqlUpdateIngreso);
        $stmtUpdateIngreso->execute([':total' => $nuevoTotal, ':id' => $idIngreso]);
        echo "<p>✅ Ingreso actualizado (ID: $idIngreso, Nuevo total: \$$nuevoTotal)</p>";
    } else {
        $sqlIngreso = "INSERT INTO ingreso (fecha, importeTotal) VALUES (:fecha, :total)";
        $stmtIngreso = $conn->prepare($sqlIngreso);
        $stmtIngreso->execute([':fecha' => $fechaVenta, ':total' => $total]);
        $idIngreso = $conn->lastInsertId();
        echo "<p>✅ Ingreso creado (ID: $idIngreso, Total: \$$total)</p>";
    }
    
    // 2. Registrar venta
    echo "<h2>🧾 Paso 2: Venta</h2>";
    $sqlVenta = "INSERT INTO venta (fechaVenta, idEmpleado, idCliente, IdIngreso) 
                 VALUES (:fecha, :empleado, :cliente, :ingreso)";
    $stmtVenta = $conn->prepare($sqlVenta);
    $stmtVenta->execute([
        ':fecha' => $fechaVenta,
        ':empleado' => $idEmpleado,
        ':cliente' => $idCliente,
        ':ingreso' => $idIngreso
    ]);
    $idVenta = $conn->lastInsertId();
    echo "<p>✅ Venta registrada (ID: $idVenta)</p>";
    
    // 3. Registrar producto de la venta
    echo "<h2>📦 Paso 3: Producto de la Venta</h2>";
    $sqlProdVenta = "INSERT INTO productoventa (idVenta, idProducto, costo, cantidad, importe)
                     VALUES (:venta, :producto, :costo, :cantidad, :importe)";
    $stmtProdVenta = $conn->prepare($sqlProdVenta);
    $stmtProdVenta->execute([
        ':venta' => $idVenta,
        ':producto' => $producto['idProducto'],
        ':costo' => $producto['precioUnitario'],
        ':cantidad' => $cantidad,
        ':importe' => $total
    ]);
    echo "<p>✅ Producto de venta registrado</p>";
    
    // 4. Actualizar stock
    echo "<h2>📊 Paso 4: Actualizar Stock</h2>";
    $nuevoStock = $producto['stock'] - $cantidad;
    $sqlStock = "UPDATE producto SET stock = :stock WHERE idProducto = :id";
    $stmtStock = $conn->prepare($sqlStock);
    $stmtStock->execute([':stock' => $nuevoStock, ':id' => $producto['idProducto']]);
    echo "<p>✅ Stock actualizado (Anterior: {$producto['stock']}, Nuevo: $nuevoStock)</p>";
    
    $conn->commit();
    
    echo "<h2 style='color:green;'>✅ VENTA COMPLETADA EXITOSAMENTE</h2>";
    echo "<ul>";
    echo "<li>ID Venta: $idVenta</li>";
    echo "<li>Total: \$$total</li>";
    echo "<li>Stock reducido correctamente</li>";
    echo "</ul>";
    
    echo "<p><a href='test-venta.php'>← Volver al test</a></p>";
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "<h2 style='color:red;'>❌ ERROR</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>