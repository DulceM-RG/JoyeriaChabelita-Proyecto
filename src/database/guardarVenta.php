<? php
/**
 * GUARDAR VENTA COMPLETA - VERSIÓN CORREGIDA
 * Archivo: api/guardar-venta.php
 * 
 * SOLO USA: venta, productoventa, producto, cliente, empleado, ingreso
 * NO USA: pedido, productopedido, proveedor (esos son para COMPRAS)
 */

session_start();
header('Content-Type: application/json');

require_once 'config/conexion.php';

// Verificar sesión
if (!isset($_SESSION['idEmpleado'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

// Obtener datos JSON
$data = json_decode(file_get_contents('php://input'), true);

// Validar datos requeridos
if (empty($data['productos']) || empty($data['total']) || empty($data['metodoPago'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Faltan datos obligatorios'
    ]);
    exit();
}

try {
    $pdo = getConexion();

    // ==========================================
    // INICIAR TRANSACCIÓN
    // ==========================================
    $pdo -> beginTransaction();

    // ==========================================
    // 1. DETERMINAR EL CLIENTE
    // ==========================================
    $idCliente = null;

    if ($data['tipoCliente'] === 'mayorista' && !empty($data['idClienteSeleccionado'])) {
        // Cliente mayorista seleccionado
        $idCliente = $data['idClienteSeleccionado'];
    } else {
        // Cliente público general - Buscar o crear
        $stmtPublico = $pdo -> prepare("
            SELECT c.idCliente 
            FROM cliente c
            INNER JOIN tipocliente tc ON c.idTipoCliente = tc.idTipoCliente
            WHERE tc.tipo = 'Público'
            LIMIT 1
        ");
        $stmtPublico -> execute();
        $clientePublico = $stmtPublico -> fetch();

        if (!$clientePublico) {
            // Obtener o crear tipo 'Público'
            $stmtTipo = $pdo -> prepare("
                SELECT idTipoCliente FROM tipocliente WHERE tipo = 'Público'
            ");
            $stmtTipo -> execute();
            $tipoPublico = $stmtTipo -> fetch();

            if (!$tipoPublico) {
                // Crear tipo 'Público'
                $stmtCrearTipo = $pdo -> prepare("
                    INSERT INTO tipocliente(tipo) VALUES('Público')
                ");
                $stmtCrearTipo -> execute();
                $idTipoPublico = $pdo -> lastInsertId();
            } else {
                $idTipoPublico = $tipoPublico['idTipoCliente'];
            }

            // Crear cliente 'Público General'
            $stmtCrearPublico = $pdo -> prepare("
                INSERT INTO cliente(idTipoCliente, nombre, apellidoPaterno, telefono)
                VALUES(: idTipoCliente, 'Público', 'General', '0000000000')
            ");
            $stmtCrearPublico -> execute([':idTipoCliente' => $idTipoPublico]);
            $idCliente = $pdo -> lastInsertId();
        } else {
            $idCliente = $clientePublico['idCliente'];
        }
    }

    // ==========================================
    // 2. OBTENER idEmpleado de la sesión
    // ==========================================
    $idEmpleado = $_SESSION['idEmpleado'];

    // ==========================================
    // 3. REGISTRAR INGRESO (dinero que entra)
    // ==========================================
    $idIngreso = null;

    // Solo si quieres llevar control de ingresos
    if ($data['metodoPago'] === 'efectivo' || $data['metodoPago'] === 'tarjeta') {
        $stmtIngreso = $pdo -> prepare("
            INSERT INTO ingreso(
            fecha,
            importeTotal
        ) VALUES(
            NOW(),
                : importeTotal
        )
        ");
        
        $stmtIngreso -> execute([
            ':importeTotal' => $data['total']
        ]);

        $idIngreso = $pdo -> lastInsertId();
    }

    // ==========================================
    // 4. INSERTAR EN LA TABLA 'venta'
    // ==========================================
    $stmtVenta = $pdo -> prepare("
        INSERT INTO venta(
        fechaVenta,
        idEmpleado,
        idCliente,
        ldIngreso
    ) VALUES(
        NOW(),
            : idEmpleado,
            : idCliente,
            : ldIngreso
    )
    ");
    
    $stmtVenta -> execute([
        ':idEmpleado' => $idEmpleado,
        ':idCliente' => $idCliente,
        ':ldIngreso' => $idIngreso ?? 0
    ]);

    $idVenta = $pdo -> lastInsertId();

    // ==========================================
    // 5. INSERTAR CADA PRODUCTO EN 'productoventa'
    // ==========================================
    $stmtProductoVenta = $pdo -> prepare("
        INSERT INTO productoventa(
        idVenta,
        idProducto,
        folio,
        cantidad,
        costoUnitario,
        importe
    ) VALUES(
            : idVenta,
            : idProducto,
            : folio,
            : cantidad,
            : costoUnitario,
            : importe
    )
    ");

    // Preparar statement para actualizar stock
    $stmtActualizarStock = $pdo -> prepare("
        UPDATE producto 
        SET stock = stock - : cantidad 
        WHERE idProducto = : idProducto
        AND stock >= : cantidad
    ");

    // Preparar statement para verificar stock antes
    $stmtVerificarStock = $pdo -> prepare("
        SELECT stock FROM producto WHERE idProducto = : idProducto
    ");
    
    $folioActual = 1;

    foreach($data['productos'] as $producto) {
        // Verificar stock disponible
        $stmtVerificarStock -> execute([':idProducto' => $producto['codigo']]);
        $productoActual = $stmtVerificarStock -> fetch();

        if (!$productoActual) {
            throw new Exception("El producto {$producto['codigo']} no existe");
        }

        if ($productoActual['stock'] < $producto['cantidad']) {
            throw new Exception("Stock insuficiente para {$producto['descripcion']}. Disponible: {$productoActual['stock']}, Solicitado: {$producto['cantidad']}");
        }

        // Insertar en productoventa
        $stmtProductoVenta -> execute([
            ':idVenta' => $idVenta,
            ':idProducto' => $producto['codigo'],
            ':folio' => $folioActual++,
            ':cantidad' => $producto['cantidad'],
            ':costoUnitario' => $producto['precio'],
            ':importe' => $producto['subtotal']
        ]);

        // Actualizar stock del producto
        $stmtActualizarStock -> execute([
            ':cantidad' => $producto['cantidad'],
            ':idProducto' => $producto['codigo']
        ]);

        // Verificar si se actualizó el stock
        if ($stmtActualizarStock -> rowCount() === 0) {
            throw new Exception("No se pudo actualizar el stock del producto: ".$producto['codigo']);
        }
    }

    // ==========================================
    // CONFIRMAR TRANSACCIÓN
    // ==========================================
    $pdo -> commit();

    // ==========================================
    // Respuesta exitosa
    // ==========================================
    echo json_encode([
        'success' => true,
        'message' => 'Venta registrada exitosamente',
        'venta' => [
            'idVenta' => $idVenta,
            'idIngreso' => $idIngreso,
            'fecha' => date('Y-m-d H:i:s'),
            'total' => $data['total'],
            'metodoPago' => $data['metodoPago'],
            'productos' => count($data['productos'])
        ]
    ]);

    cerrarConexion($pdo);

} catch (Exception $e) {
    // ==========================================
    // REVERTIR TRANSACCIÓN en caso de error
    // ==========================================
    if (isset($pdo) && $pdo -> inTransaction()) {
        $pdo -> rollBack();
    }

    error_log("Error en guardar-venta: ".$e -> getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e -> getMessage()
    ]);
}
?>