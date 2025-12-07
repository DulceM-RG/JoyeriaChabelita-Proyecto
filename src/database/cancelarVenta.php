<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'connection.php';

try {
    $pdo = ConexionDB::setConnection();
    
    // Leer el cuerpo de la petición
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // ============================================
    // ACTION: OBTENER DETALLES DE LA VENTA
    // ============================================
    if (isset($data['action']) && $data['action'] === 'obtenerDetallesVenta') {
        $idVenta = intval($data['idVenta']);
        
        // Obtener información de la venta
        $stmtVenta = $pdo->prepare("
            SELECT 
                v.idVenta,
                v.fechaVenta,
                DATEDIFF(CURDATE(), v.fechaVenta) as diasTranscurridos,
                CONCAT(c.nombre, ' ', COALESCE(c.apellidoPaterno, ''), ' ', COALESCE(c.apellidoMaterno, '')) as cliente,
                CONCAT(e.nombre, ' ', e.apellidoPaterno) as empleado,
                i.importeTotal,
                i.metodoPago,
                COUNT(pv.idProductoVenta) as totalProductos,
                SUM(pv.cantidad) as totalCantidad
            FROM venta v
            LEFT JOIN cliente c ON v.idCliente = c.idCliente
            LEFT JOIN empleado e ON v.idEmpleado = e.idEmpleado
            LEFT JOIN ingreso i ON v.idIngreso = i.idIngreso
            LEFT JOIN productoventa pv ON v.idVenta = pv.idVenta
            WHERE v.idVenta = :idVenta
            GROUP BY v.idVenta
        ");
        
        $stmtVenta->execute(['idVenta' => $idVenta]);
        $venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);
        
        if (!$venta) {
            echo json_encode([
                'success' => false,
                'message' => 'Venta no encontrada'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Validar si la venta puede cancelarse (máximo 3 días)
        $diasTranscurridos = $venta['diasTranscurridos'];
        if ($diasTranscurridos > 3) {
            echo json_encode([
                'success' => false,
                'message' => 'Esta venta no puede cancelarse. Solo se permiten cancelaciones dentro de 3 días.',
                'diasTranscurridos' => $diasTranscurridos
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Obtener detalles de productos vendidos
        $stmtProductos = $pdo->prepare("
            SELECT 
                pv.idProductoVenta,
                pv.idProducto,
                pr.descripcion,
                pr.stock as stockActual,
                pv.cantidad,
                pv.costo,
                pv.importe,
                c.nombre as categoria
            FROM productoventa pv
            INNER JOIN producto pr ON pv.idProducto = pr.idProducto
            LEFT JOIN categoria c ON pr.idCategoria = c.idCategoria
            WHERE pv.idVenta = :idVenta
            ORDER BY pv.idProductoVenta
        ");
        
        $stmtProductos->execute(['idVenta' => $idVenta]);
        $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'venta' => $venta,
            'productos' => $productos
        ], JSON_UNESCAPED_UNICODE);
        
    } 
    // ============================================
    // ACTION: CANCELAR VENTA (ELIMINAR Y DEVOLVER)
    // ============================================
    else if (isset($data['action']) && $data['action'] === 'cancelarVenta') {
        $idVenta = intval($data['idVenta']);
        
        // Comenzar transacción
        $pdo->beginTransaction();
        
        try {
            // PASO 1: Obtener información de la venta
            $stmtVentaInfo = $pdo->prepare("
                SELECT idIngreso, fechaVenta
                FROM venta
                WHERE idVenta = :idVenta
            ");
            $stmtVentaInfo->execute(['idVenta' => $idVenta]);
            $ventaInfo = $stmtVentaInfo->fetch(PDO::FETCH_ASSOC);
            
            if (!$ventaInfo) {
                throw new Exception('Venta no encontrada');
            }
            
            // Validar que tenga máximo 3 días
            $diasTranscurridos = (new DateTime($ventaInfo['fechaVenta']))->diff(new DateTime())->days;
            if ($diasTranscurridos > 3) {
                throw new Exception('Esta venta no puede cancelarse. Solo se permiten cancelaciones dentro de 3 días.');
            }
            
            // PASO 2: Obtener todos los productos de la venta para devolverlos al inventario
            $stmtProductos = $pdo->prepare("
                SELECT idProducto, cantidad
                FROM productoventa
                WHERE idVenta = :idVenta
            ");
            $stmtProductos->execute(['idVenta' => $idVenta]);
            $productosVenta = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
            
            // PASO 3: Devolver inventario
            foreach ($productosVenta as $prod) {
                $stmtDevolver = $pdo->prepare("
                    UPDATE producto
                    SET stock = stock + :cantidad
                    WHERE idProducto = :idProducto
                ");
                $stmtDevolver->execute([
                    'cantidad' => $prod['cantidad'],
                    'idProducto' => $prod['idProducto']
                ]);
            }
            
            // PASO 4: Eliminar productoventa
            $stmtDeleteProductoVenta = $pdo->prepare("
                DELETE FROM productoventa
                WHERE idVenta = :idVenta
            ");
            $stmtDeleteProductoVenta->execute(['idVenta' => $idVenta]);
            
            // PASO 5: Eliminar venta
            $stmtDeleteVenta = $pdo->prepare("
                DELETE FROM venta
                WHERE idVenta = :idVenta
            ");
            $stmtDeleteVenta->execute(['idVenta' => $idVenta]);
            
            // PASO 6: Eliminar ingreso
            if ($ventaInfo['idIngreso']) {
                $stmtDeleteIngreso = $pdo->prepare("
                    DELETE FROM ingreso
                    WHERE idIngreso = :idIngreso
                ");
                $stmtDeleteIngreso->execute(['idIngreso' => $ventaInfo['idIngreso']]);
            }
            
            // PASO 7: Recalcular corte_caja
            $fechaVenta = $ventaInfo['fechaVenta'];
            
            // Obtener nuevos totales del día
            $stmtNuevoTotal = $pdo->prepare("
                SELECT COALESCE(SUM(importeTotal), 0) as totalDia
                FROM ingreso
                WHERE fecha = :fecha
            ");
            $stmtNuevoTotal->execute(['fecha' => $fechaVenta]);
            $nuevoTotalDia = $stmtNuevoTotal->fetch()['totalDia'];
            
            $stmtNuevoEfectivo = $pdo->prepare("
                SELECT COALESCE(SUM(importeTotal), 0) as efectivo
                FROM ingreso
                WHERE fecha = :fecha AND metodoPago = 'efectivo'
            ");
            $stmtNuevoEfectivo->execute(['fecha' => $fechaVenta]);
            $nuevoEfectivo = $stmtNuevoEfectivo->fetch()['efectivo'];
            
            $stmtNuevasTarjeta = $pdo->prepare("
                SELECT COALESCE(SUM(importeTotal), 0) as tarjeta
                FROM ingreso
                WHERE fecha = :fecha AND metodoPago = 'tarjeta'
            ");
            $stmtNuevasTarjeta->execute(['fecha' => $fechaVenta]);
            $nuevoTarjeta = $stmtNuevasTarjeta->fetch()['tarjeta'];
            
            $stmtNuevosVentas = $pdo->prepare("
                SELECT COUNT(*) as ventasRealizadas
                FROM venta
                WHERE fechaVenta = :fecha
            ");
            $stmtNuevosVentas->execute(['fecha' => $fechaVenta]);
            $nuevoVentasRealizadas = $stmtNuevosVentas->fetch()['ventasRealizadas'];
            
            $stmtNuevoProductos = $pdo->prepare("
                SELECT COALESCE(SUM(pv.cantidad), 0) as productosVendidos
                FROM productoventa pv
                INNER JOIN venta v ON pv.idVenta = v.idVenta
                WHERE v.fechaVenta = :fecha
            ");
            $stmtNuevoProductos->execute(['fecha' => $fechaVenta]);
            $nuevoProductosVendidos = $stmtNuevoProductos->fetch()['productosVendidos'];
            
            $stmtNuevoEmpleados = $pdo->prepare("
                SELECT COUNT(DISTINCT idEmpleado) as empleadosActivos
                FROM venta
                WHERE fechaVenta = :fecha
            ");
            $stmtNuevoEmpleados->execute(['fecha' => $fechaVenta]);
            $nuevoEmpleadosActivos = $stmtNuevoEmpleados->fetch()['empleadosActivos'];
            
            // Actualizar corte_caja si existe
            $stmtUpdateCorte = $pdo->prepare("
                UPDATE corte_caja
                SET 
                    totalDia = :totalDia,
                    efectivo = :efectivo,
                    tarjeta = :tarjeta,
                    ventasRealizadas = :ventasRealizadas,
                    productosVendidos = :productosVendidos,
                    empleadosActivos = :empleadosActivos,
                    fechaCierre = NOW()
                WHERE fecha = :fecha
            ");
            
            $stmtUpdateCorte->execute([
                'totalDia' => $nuevoTotalDia,
                'efectivo' => $nuevoEfectivo,
                'tarjeta' => $nuevoTarjeta,
                'ventasRealizadas' => $nuevoVentasRealizadas,
                'productosVendidos' => $nuevoProductosVendidos,
                'empleadosActivos' => $nuevoEmpleadosActivos,
                'fecha' => $fechaVenta
            ]);
            
            // Confirmar transacción
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Venta cancelada correctamente. Inventario devuelto y corte de caja actualizado.',
                'idVenta' => $idVenta
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'message' => 'Error al cancelar: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    } 
    else {
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>