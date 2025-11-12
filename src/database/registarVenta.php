<?php
session_start();
header('Content-Type: application/json');
require_once 'connection.php';

error_log("=== INICIO registrarVenta.php ===");

try {
    // Verificar que el usuario esté autenticado
    if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['idEmpleado'])) {
        error_log("❌ Sin sesión activa");
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "error" => "No hay sesión activa. Por favor, inicie sesión."
        ]);
        exit;
    }
    
    $idEmpleado = $_SESSION['usuario']['idEmpleado'];
    error_log("Empleado ID: " . $idEmpleado);
    
    // Recibir datos de la venta
    $entrada = json_decode(file_get_contents('php://input'), true);
    
    error_log("Datos recibidos: " . print_r($entrada, true));
    
    $idCliente = $entrada['idCliente'] ?? null;
    $productos = $entrada['productos'] ?? [];
    $metodoPago = $entrada['metodoPago'] ?? 'efectivo';
    $efectivoRecibido = $entrada['efectivoRecibido'] ?? null;
    $cambio = $entrada['cambio'] ?? null;
    
    // Validaciones básicas
    if (empty($productos)) {
        error_log("❌ Sin productos");
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "La venta debe contener al menos un producto."
        ]);
        exit;
    }
    
    if (!$idCliente) {
        error_log("❌ Sin cliente");
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Debe seleccionar un cliente."
        ]);
        exit;
    }
    
    $conn = ConexionDB::setConnection();
    $conn->beginTransaction();
    
    error_log("Transacción iniciada");
    
    // ==================== PASO 1: CALCULAR TOTAL Y VALIDAR STOCK ====================
    $montoTotal = 0;
    $productosValidados = [];
    
    foreach ($productos as $prod) {
        $idProducto = $prod['idProducto'] ?? '';
        $cantidad = intval($prod['cantidad'] ?? 0);
        
        error_log("Procesando producto: " . $idProducto . " x " . $cantidad);
        
        if (empty($idProducto) || $cantidad <= 0) {
            throw new Exception("Producto inválido en la lista.");
        }
        
        // Verificar stock disponible
        $sqlStock = "SELECT idProducto, stock, precioUnitario, descripcion FROM producto WHERE idProducto = :idProducto";
        $stmtStock = $conn->prepare($sqlStock);
        $stmtStock->execute([':idProducto' => $idProducto]);
        $producto = $stmtStock->fetch(PDO::FETCH_ASSOC);
        
        if (!$producto) {
            throw new Exception("Producto no encontrado: " . $idProducto);
        }
        
        if ($producto['stock'] < $cantidad) {
            throw new Exception("Stock insuficiente para: " . $producto['descripcion'] . ". Stock disponible: " . $producto['stock']);
        }
        
        $precioUnitario = floatval($producto['precioUnitario']);
        $subtotal = $precioUnitario * $cantidad;
        $montoTotal += $subtotal;
        
        $productosValidados[] = [
            'idProducto' => $idProducto,
            'cantidad' => $cantidad,
            'precioUnitario' => $precioUnitario,
            'subtotal' => $subtotal,
            'stockActual' => $producto['stock']
        ];
    }
    
    error_log("Total calculado: $" . $montoTotal);
    
    // ==================== PASO 2: REGISTRAR INGRESO ====================
    $fechaVenta = date('Y-m-d');
    
    // Verificar si ya existe un ingreso para hoy
    $sqlCheckIngreso = "SELECT idIngreso, importeTotal FROM ingreso WHERE fecha = :fecha";
    $stmtCheckIngreso = $conn->prepare($sqlCheckIngreso);
    $stmtCheckIngreso->execute([':fecha' => $fechaVenta]);
    $ingresoExistente = $stmtCheckIngreso->fetch(PDO::FETCH_ASSOC);
    
    if ($ingresoExistente) {
        // Actualizar el ingreso existente
        $idIngreso = $ingresoExistente['idIngreso'];
        $nuevoTotal = floatval($ingresoExistente['importeTotal']) + $montoTotal;
        
        $sqlUpdateIngreso = "UPDATE ingreso SET importeTotal = :importeTotal WHERE idIngreso = :idIngreso";
        $stmtUpdateIngreso = $conn->prepare($sqlUpdateIngreso);
        $stmtUpdateIngreso->execute([
            ':importeTotal' => $nuevoTotal,
            ':idIngreso' => $idIngreso
        ]);
        
        error_log("Ingreso actualizado. ID: " . $idIngreso . ", Nuevo total: $" . $nuevoTotal);
    } else {
        // Crear nuevo ingreso
        $sqlIngreso = "INSERT INTO ingreso (fecha, importeTotal) VALUES (:fecha, :importeTotal)";
        $stmtIngreso = $conn->prepare($sqlIngreso);
        $stmtIngreso->execute([
            ':fecha' => $fechaVenta,
            ':importeTotal' => $montoTotal
        ]);
        $idIngreso = $conn->lastInsertId();
        
        error_log("Ingreso creado. ID: " . $idIngreso);
    }
    
    // ==================== PASO 3: REGISTRAR VENTA ====================
    // ⚠️ IMPORTANTE: El campo es IdIngreso (con I mayúscula) según tu BD
    $sqlVenta = "INSERT INTO venta (fechaVenta, idEmpleado, idCliente, IdIngreso) 
                 VALUES (:fechaVenta, :idEmpleado, :idCliente, :idIngreso)";
    $stmtVenta = $conn->prepare($sqlVenta);
    $stmtVenta->execute([
        ':fechaVenta' => $fechaVenta,
        ':idEmpleado' => $idEmpleado,
        ':idCliente' => $idCliente,
        ':idIngreso' => $idIngreso
    ]);
    $idVenta = $conn->lastInsertId();
    
    error_log("Venta registrada. ID: " . $idVenta);
    
    // ==================== PASO 4: REGISTRAR PRODUCTOS DE LA VENTA Y ACTUALIZAR STOCK ====================
    foreach ($productosValidados as $prod) {
        // Insertar en productoventa
        $sqlProductoVenta = "INSERT INTO productoventa (idVenta, idProducto, costo, cantidad, importe) 
                             VALUES (:idVenta, :idProducto, :costo, :cantidad, :importe)";
        $stmtProductoVenta = $conn->prepare($sqlProductoVenta);
        $stmtProductoVenta->execute([
            ':idVenta' => $idVenta,
            ':idProducto' => $prod['idProducto'],
            ':costo' => $prod['precioUnitario'],
            ':cantidad' => $prod['cantidad'],
            ':importe' => $prod['subtotal']
        ]);
        
        error_log("ProductoVenta insertado: " . $prod['idProducto']);
        
        // ⚠️ CRÍTICO: Actualizar stock del producto (DISMINUIR)
        $nuevoStock = $prod['stockActual'] - $prod['cantidad'];
        $sqlUpdateStock = "UPDATE producto SET stock = :stock WHERE idProducto = :idProducto";
        $stmtUpdateStock = $conn->prepare($sqlUpdateStock);
        $stmtUpdateStock->execute([
            ':stock' => $nuevoStock,
            ':idProducto' => $prod['idProducto']
        ]);
        
        error_log("Stock actualizado: " . $prod['idProducto'] . " - Stock anterior: " . $prod['stockActual'] . " - Nuevo stock: " . $nuevoStock);
    }
    
    // ==================== CONFIRMAR TRANSACCIÓN ====================
    $conn->commit();
    
    error_log("✅ Transacción completada exitosamente");
    
    // Obtener datos del empleado para el ticket
    $sqlEmpleado = "SELECT nombre, apellidoPaterno, apellidoMaterno FROM empleado WHERE idEmpleado = :idEmpleado";
    $stmtEmpleado = $conn->prepare($sqlEmpleado);
    $stmtEmpleado->execute([':idEmpleado' => $idEmpleado]);
    $empleado = $stmtEmpleado->fetch(PDO::FETCH_ASSOC);
    
    // Obtener datos del cliente
    $sqlCliente = "SELECT c.*, tc.tipo FROM cliente c 
                   INNER JOIN tipocliente tc ON c.idTipoCliente = tc.idTipoCliente 
                   WHERE c.idCliente = :idCliente";
    $stmtCliente = $conn->prepare($sqlCliente);
    $stmtCliente->execute([':idCliente' => $idCliente]);
    $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);
    
    $nombreCliente = 'Público General';
    if ($cliente['nombre']) {
        $nombreCliente = trim($cliente['nombre'] . ' ' . $cliente['apellidoPaterno'] . ' ' . $cliente['apellidoMaterno']);
    }
    
    // Respuesta exitosa
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "mensaje" => "Venta registrada exitosamente",
        "venta" => [
            "idVenta" => $idVenta,
            "fechaVenta" => $fechaVenta,
            "montoTotal" => number_format($montoTotal, 2, '.', ''),
            "metodoPago" => $metodoPago,
            "efectivoRecibido" => $efectivoRecibido,
            "cambio" => $cambio,
            "cantidadProductos" => count($productosValidados),
            "idIngreso" => $idIngreso
        ],
        "empleado" => [
            "nombre" => $empleado['nombre'] . ' ' . $empleado['apellidoPaterno'] . ' ' . $empleado['apellidoMaterno']
        ],
        "cliente" => [
            "tipo" => $cliente['tipo'],
            "nombre" => $nombreCliente
        ]
    ]);
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
        error_log("⚠️ Transacción revertida");
    }
    error_log("❌ Error PDO: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error de base de datos: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
        error_log("⚠️ Transacción revertida");
    }
    error_log("❌ Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}

error_log("=== FIN registrarVenta.php ===");
?>