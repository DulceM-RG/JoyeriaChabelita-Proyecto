<?php
/**
 * GUARDAR VENTA COMPLETA - ERROR HY093 CORREGIDO
 * Archivo: database/guardarVenta.php
 */

session_start();
header('Content-Type: application/json');

require_once 'connection.php';

error_log("=== INICIO guardarVenta.php ===");

// Validar sesión
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['idEmpleado'])) {
    error_log("❌ Error: No hay sesión de empleado");
    error_log("Contenido de SESSION: " . print_r($_SESSION, true));
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado - Inicie sesión']);
    exit();
}

// Obtener datos JSON
$raw_input = file_get_contents('php://input');
error_log("📥 Datos recibidos: " . substr($raw_input, 0, 500)); // Solo primeros 500 chars

$data = json_decode($raw_input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("❌ Error JSON: " . json_last_error_msg());
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Error JSON: ' . json_last_error_msg()]);
    exit();
}

// Validar datos
if (empty($data['productos'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No hay productos en la venta']);
    exit();
}

if (!isset($data['total']) || $data['total'] <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Total inválido']);
    exit();
}

if (empty($data['metodoPago'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Falta método de pago']);
    exit();
}

try {
    $pdo = ConexionDB::setConnection();
    error_log("✅ Conexión establecida");

    $pdo->beginTransaction();
    error_log("🔄 Transacción iniciada");

    $idEmpleado = $_SESSION['usuario']['idEmpleado'];
    $idCliente = $data['idCliente'] ?? 1;
    
    error_log("👤 Empleado ID: $idEmpleado");
    error_log("🧑 Cliente ID: $idCliente");

    // ==========================================
    // VALIDAR CLIENTE
    // ==========================================
    $stmtCliente = $pdo->prepare("SELECT idCliente FROM cliente WHERE idCliente = ?");
    $stmtCliente->execute([$idCliente]);
    
    if (!$stmtCliente->fetch()) {
        throw new Exception("Cliente ID $idCliente no existe");
    }

    // ==========================================
    // REGISTRAR INGRESO - VERSIÓN CORREGIDA
    // ==========================================
    error_log("💰 Intentando insertar ingreso con monto: " . $data['total']);
    
    // ✅ CORRECCIÓN: Usar ? en lugar de parámetros nombrados
    $sqlIngreso = "INSERT INTO ingreso(fecha, importeTotal) VALUES(NOW(), ?)";
    error_log("SQL Ingreso: $sqlIngreso");
    
    $stmtIngreso = $pdo->prepare($sqlIngreso);
    
    // Ejecutar con array indexado
    $resultIngreso = $stmtIngreso->execute([$data['total']]);
    
    if (!$resultIngreso) {
        $errorInfo = $stmtIngreso->errorInfo();
        error_log("❌ Error al insertar ingreso: " . print_r($errorInfo, true));
        throw new Exception("Error al registrar ingreso: " . $errorInfo[2]);
    }
    
    $idIngreso = $pdo->lastInsertId();
    
    if (!$idIngreso || $idIngreso == 0) {
        throw new Exception("No se obtuvo ID de ingreso");
    }
    
    error_log("✅ Ingreso registrado. ID: $idIngreso");

    // ==========================================
    // INSERTAR VENTA
    // ==========================================
    $sqlVenta = "INSERT INTO venta(fechaVenta, idEmpleado, idCliente, idIngreso) VALUES(NOW(), ?, ?, ?)";
    error_log("SQL Venta: $sqlVenta");
    
    $stmtVenta = $pdo->prepare($sqlVenta);
    $resultVenta = $stmtVenta->execute([$idEmpleado, $idCliente, $idIngreso]);
    
    if (!$resultVenta) {
        $errorInfo = $stmtVenta->errorInfo();
        error_log("❌ Error al insertar venta: " . print_r($errorInfo, true));
        throw new Exception("Error al registrar venta: " . $errorInfo[2]);
    }
    
    $idVenta = $pdo->lastInsertId();
    
    if (!$idVenta || $idVenta == 0) {
        throw new Exception("No se obtuvo ID de venta");
    }
    
    error_log("✅ Venta registrada. ID: $idVenta");

    // ==========================================
    // INSERTAR PRODUCTOS
    // ==========================================
    $sqlProductoVenta = "INSERT INTO productoventa(idVenta, idProducto, cantidad, costo, importe) VALUES(?, ?, ?, ?, ?)";
    $stmtProductoVenta = $pdo->prepare($sqlProductoVenta);

    $sqlActualizarStock = "UPDATE producto SET stock = stock - ? WHERE idProducto = ? AND stock >= ?";
    $stmtActualizarStock = $pdo->prepare($sqlActualizarStock);

    $sqlVerificarStock = "SELECT stock, descripcion FROM producto WHERE idProducto = ?";
    $stmtVerificarStock = $pdo->prepare($sqlVerificarStock);

    $productosInsertados = 0;

    foreach($data['productos'] as $producto) {
        $idProducto = $producto['codigo'];
        $cantidad = $producto['cantidad'];
        $precio = $producto['precio'];
        $subtotal = $producto['subtotal'];
        
        error_log("📦 Procesando: ID=$idProducto, Cant=$cantidad, Precio=$precio");

        // Verificar stock
        $stmtVerificarStock->execute([$idProducto]);
        $productoActual = $stmtVerificarStock->fetch(PDO::FETCH_ASSOC);

        if (!$productoActual) {
            throw new Exception("Producto $idProducto no existe");
        }

        if ($productoActual['stock'] < $cantidad) {
            throw new Exception(
                "Stock insuficiente para {$productoActual['descripcion']}. " .
                "Disponible: {$productoActual['stock']}, Solicitado: $cantidad"
            );
        }

        // Insertar en productoventa
        $resultProducto = $stmtProductoVenta->execute([
            $idVenta,
            $idProducto,
            $cantidad,
            $precio,
            $subtotal
        ]);
        
        if (!$resultProducto) {
            $errorInfo = $stmtProductoVenta->errorInfo();
            throw new Exception("Error al insertar producto: " . $errorInfo[2]);
        }

        // Actualizar stock
        $resultStock = $stmtActualizarStock->execute([$cantidad, $idProducto, $cantidad]);
        
        if (!$resultStock || $stmtActualizarStock->rowCount() === 0) {
            throw new Exception("No se pudo actualizar stock de producto $idProducto");
        }

        $productosInsertados++;
        error_log("  ✅ Producto insertado y stock actualizado");
    }

    // ==========================================
    // CONFIRMAR
    // ==========================================
    $pdo->commit();
    error_log("✅ COMMIT exitoso. Productos: $productosInsertados");

    echo json_encode([
        'success' => true,
        'message' => 'Venta registrada exitosamente',
        'venta' => [
            'idVenta' => $idVenta,
            'idIngreso' => $idIngreso,
            'fecha' => date('Y-m-d H:i:s'),
            'total' => $data['total'],
            'metodoPago' => $data['metodoPago'],
            'productos' => $productosInsertados
        ]
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
        error_log("🔙 ROLLBACK ejecutado");
    }

    error_log("❌ ERROR: " . $e->getMessage());
    error_log("❌ Trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

error_log("=== FIN guardarVenta.php ===");
?>