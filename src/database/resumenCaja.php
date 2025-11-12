<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'connection.php';

try {
    $pdo = ConexionDB::setConnection();
    
    if (isset($_GET['action']) && $_GET['action'] === 'obtenerResumen') {
        
        // Obtener la fecha del parámetro o usar la fecha actual
        $fechaHoy = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
        
        // 1. Total del día (suma de todos los ingresos del día)
        $stmtTotal = $pdo->prepare("
            SELECT COALESCE(SUM(importeTotal), 0) as totalDia
            FROM ingreso
            WHERE fecha = :fecha
        ");
        $stmtTotal->execute(['fecha' => $fechaHoy]);
        $totalDia = $stmtTotal->fetch()['totalDia'];
        
        // 2. Total en efectivo
        $stmtEfectivo = $pdo->prepare("
            SELECT COALESCE(SUM(importeTotal), 0) as efectivo
            FROM ingreso
            WHERE fecha = :fecha AND metodoPago = 'efectivo'
        ");
        $stmtEfectivo->execute(['fecha' => $fechaHoy]);
        $efectivo = $stmtEfectivo->fetch()['efectivo'];
        
        // 3. Total en tarjeta
        $stmtTarjeta = $pdo->prepare("
            SELECT COALESCE(SUM(importeTotal), 0) as tarjeta
            FROM ingreso
            WHERE fecha = :fecha AND metodoPago = 'tarjeta'
        ");
        $stmtTarjeta->execute(['fecha' => $fechaHoy]);
        $tarjeta = $stmtTarjeta->fetch()['tarjeta'];
        
        // 4. Ventas realizadas (contar ventas del día)
        $stmtVentas = $pdo->prepare("
            SELECT COUNT(*) as ventasRealizadas
            FROM venta
            WHERE fechaVenta = :fecha
        ");
        $stmtVentas->execute(['fecha' => $fechaHoy]);
        $ventasRealizadas = $stmtVentas->fetch()['ventasRealizadas'];
        
        // 5. Productos vendidos (suma de cantidades en productoVenta del día)
        $stmtProductos = $pdo->prepare("
            SELECT COALESCE(SUM(pv.cantidad), 0) as productosVendidos
            FROM productoVenta pv
            INNER JOIN venta v ON pv.idVenta = v.idVenta
            WHERE v.fechaVenta = :fecha
        ");
        $stmtProductos->execute(['fecha' => $fechaHoy]);
        $productosVendidos = $stmtProductos->fetch()['productosVendidos'];
        
        // 6. Empleados activos (contar empleados únicos que hicieron ventas hoy)
        $stmtEmpleados = $pdo->prepare("
            SELECT COUNT(DISTINCT idEmpleado) as empleadosActivos
            FROM venta
            WHERE fechaVenta = :fecha
        ");
        $stmtEmpleados->execute(['fecha' => $fechaHoy]);
        $empleadosActivos = $stmtEmpleados->fetch()['empleadosActivos'];
        
        // Respuesta JSON
        echo json_encode([
            'success' => true,
            'totalDia' => floatval($totalDia),
            'efectivo' => floatval($efectivo),
            'tarjeta' => floatval($tarjeta),
            'ventasRealizadas' => intval($ventasRealizadas),
            'productosVendidos' => intval($productosVendidos),
            'empleadosActivos' => intval($empleadosActivos)
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
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