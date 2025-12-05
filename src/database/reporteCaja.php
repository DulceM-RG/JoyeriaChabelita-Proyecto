<?php
/**
 * ==========================================
 * REPORTE DETALLADO DE CORTE DE CAJA
 * VERSIÓN MEJORADA CON ANÁLISIS COMPLETO
 * ==========================================
 */

header('Content-Type: text/html; charset=utf-8');

// ZONA HORARIA PARA MÉXICO
date_default_timezone_set('America/Mexico_City');

ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    require_once 'connection.php';
    $pdo = ConexionDB::setConnection();
    
    // Obtener fecha
    $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

    // ==================== RESUMEN GENERAL ====================
    
    // Total del día
    $stmtTotal = $pdo->prepare("
        SELECT COALESCE(SUM(i.importeTotal), 0) as totalDia 
        FROM venta v
        INNER JOIN ingreso i ON v.idIngreso = i.idIngreso
        WHERE DATE(v.fechaVenta) = :fecha
    ");
    $stmtTotal->execute(['fecha' => $fecha]);
    $totalDia = floatval($stmtTotal->fetch(PDO::FETCH_ASSOC)['totalDia'] ?? 0);
    
    // Efectivo
    $stmtEfectivo = $pdo->prepare("
        SELECT COALESCE(SUM(i.importeTotal), 0) as efectivo 
        FROM venta v
        INNER JOIN ingreso i ON v.idIngreso = i.idIngreso
        WHERE DATE(v.fechaVenta) = :fecha AND i.metodoPago = 'efectivo'
    ");
    $stmtEfectivo->execute(['fecha' => $fecha]);
    $efectivo = floatval($stmtEfectivo->fetch(PDO::FETCH_ASSOC)['efectivo'] ?? 0);
    
    // Tarjeta
    $stmtTarjeta = $pdo->prepare("
        SELECT COALESCE(SUM(i.importeTotal), 0) as tarjeta 
        FROM venta v
        INNER JOIN ingreso i ON v.idIngreso = i.idIngreso
        WHERE DATE(v.fechaVenta) = :fecha AND i.metodoPago = 'tarjeta'
    ");
    $stmtTarjeta->execute(['fecha' => $fecha]);
    $tarjeta = floatval($stmtTarjeta->fetch(PDO::FETCH_ASSOC)['tarjeta'] ?? 0);
    
    // Ventas realizadas
    $stmtVentas = $pdo->prepare("
        SELECT COUNT(*) as ventasRealizadas 
        FROM venta 
        WHERE DATE(fechaVenta) = :fecha
    ");
    $stmtVentas->execute(['fecha' => $fecha]);
    $ventasRealizadas = intval($stmtVentas->fetch(PDO::FETCH_ASSOC)['ventasRealizadas'] ?? 0);
    
    // Productos vendidos
    $stmtProductos = $pdo->prepare("
        SELECT COALESCE(SUM(pv.cantidad), 0) as productosVendidos 
        FROM productoventa pv 
        INNER JOIN venta v ON pv.idVenta = v.idVenta 
        WHERE DATE(v.fechaVenta) = :fecha
    ");
    $stmtProductos->execute(['fecha' => $fecha]);
    $productosVendidos = intval($stmtProductos->fetch(PDO::FETCH_ASSOC)['productosVendidos'] ?? 0);
    
    // Empleados activos
    $stmtEmpleados = $pdo->prepare("
        SELECT COUNT(DISTINCT v.idEmpleado) as empleadosActivos 
        FROM venta v
        WHERE DATE(v.fechaVenta) = :fecha
    ");
    $stmtEmpleados->execute(['fecha' => $fecha]);
    $empleadosActivos = intval($stmtEmpleados->fetch(PDO::FETCH_ASSOC)['empleadosActivos'] ?? 0);
    
    // Promedios
    $promedioVenta = $ventasRealizadas > 0 ? $totalDia / $ventasRealizadas : 0;
    
    // Contar transacciones efectivo
    $stmtEfectivoCount = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM venta v
        INNER JOIN ingreso i ON v.idIngreso = i.idIngreso
        WHERE DATE(v.fechaVenta) = :fecha AND i.metodoPago = 'efectivo'
    ");
    $stmtEfectivoCount->execute(['fecha' => $fecha]);
    $efectivoCount = intval($stmtEfectivoCount->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
    
    // Contar transacciones tarjeta
    $stmtTarjetaCount = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM venta v
        INNER JOIN ingreso i ON v.idIngreso = i.idIngreso
        WHERE DATE(v.fechaVenta) = :fecha AND i.metodoPago = 'tarjeta'
    ");
    $stmtTarjetaCount->execute(['fecha' => $fecha]);
    $tarjetaCount = intval($stmtTarjetaCount->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    // ==================== ANÁLISIS DE GANANCIAS ====================
    
    $stmtGanancias = $pdo->prepare("
        SELECT 
            COALESCE(SUM(pv.importe), 0) as totalVentas,
            COALESCE(SUM(pv.cantidad * p.precioCompra), 0) as totalCostos
        FROM productoventa pv
        INNER JOIN venta v ON pv.idVenta = v.idVenta
        INNER JOIN producto p ON pv.idProducto = p.idProducto
        WHERE DATE(v.fechaVenta) = :fecha
    ");
    $stmtGanancias->execute(['fecha' => $fecha]);
    $ganancias = $stmtGanancias->fetch(PDO::FETCH_ASSOC);
    $totalVentas = floatval($ganancias['totalVentas'] ?? 0);
    $totalCostos = floatval($ganancias['totalCostos'] ?? 0);
    $gananciaTotal = $totalVentas - $totalCostos;
    $margenGanancia = $totalVentas > 0 ? ($gananciaTotal / $totalVentas) * 100 : 0;

    // ==================== TOP VENDEDORES ====================
    
    $stmtTopVendedores = $pdo->prepare("
        SELECT 
            e.nombre,
            e.apellidoPaterno,
            COUNT(v.idVenta) as ventasRealizadas,
            COALESCE(SUM(i.importeTotal), 0) as totalVendido
        FROM venta v
        INNER JOIN empleado e ON v.idEmpleado = e.idEmpleado
        INNER JOIN ingreso i ON v.idIngreso = i.idIngreso
        WHERE DATE(v.fechaVenta) = :fecha
        GROUP BY e.idEmpleado, e.nombre, e.apellidoPaterno
        ORDER BY totalVendido DESC
        LIMIT 5
    ");
    $stmtTopVendedores->execute(['fecha' => $fecha]);
    $topVendedores = $stmtTopVendedores->fetchAll(PDO::FETCH_ASSOC);

    // ==================== PRODUCTOS MÁS VENDIDOS ====================
    
    $stmtTopProductos = $pdo->prepare("
        SELECT 
            p.idProducto,
            p.descripcion,
            c.nombre as categoria,
            SUM(pv.cantidad) as cantidadVendida,
            SUM(pv.importe) as totalVendido
        FROM productoventa pv
        INNER JOIN venta v ON pv.idVenta = v.idVenta
        INNER JOIN producto p ON pv.idProducto = p.idProducto
        LEFT JOIN categoria c ON p.idCategoria = c.idCategoria
        WHERE DATE(v.fechaVenta) = :fecha
        GROUP BY p.idProducto, p.descripcion, c.nombre
        ORDER BY cantidadVendida DESC
        LIMIT 5
    ");
    $stmtTopProductos->execute(['fecha' => $fecha]);
    $topProductos = $stmtTopProductos->fetchAll(PDO::FETCH_ASSOC);

    // ==================== DETALLES DE VENTAS ====================
    
    $sqlVentas = "
        SELECT 
            v.idVenta,
            v.fechaVenta,
            COALESCE(CONCAT(c.nombre, ' ', c.apellidoPaterno), 'Público General') as clienteNombre,
            COALESCE(CONCAT(e.nombre, ' ', e.apellidoPaterno), 'Sin Vendedor') as empleadoNombre,
            tc.tipo as tipoCliente,
            i.metodoPago,
            i.importeTotal
        FROM venta v
        INNER JOIN ingreso i ON v.idIngreso = i.idIngreso
        LEFT JOIN cliente c ON v.idCliente = c.idCliente
        LEFT JOIN tipocliente tc ON c.idTipoCliente = tc.idTipoCliente
        LEFT JOIN empleado e ON v.idEmpleado = e.idEmpleado
        WHERE DATE(v.fechaVenta) = :fecha
        ORDER BY v.fechaVenta ASC
    ";
    
    $stmtVentasDetalle = $pdo->prepare($sqlVentas);
    $stmtVentasDetalle->execute(['fecha' => $fecha]);
    $ventasDetalle = $stmtVentasDetalle->fetchAll(PDO::FETCH_ASSOC);

    // ==================== DETALLES LÍNEA POR LÍNEA ====================
    
    $sqlProductos = "
        SELECT 
            v.idVenta,
            p.idProducto,
            p.descripcion as productName,
            p.kilataje,
            pv.cantidad,
            pv.costo as precioVenta,
            p.precioCompra,
            pv.importe as subtotal,
            (pv.importe - (pv.cantidad * p.precioCompra)) as ganancia
        FROM venta v
        INNER JOIN productoventa pv ON v.idVenta = pv.idVenta
        INNER JOIN producto p ON pv.idProducto = p.idProducto
        WHERE DATE(v.fechaVenta) = :fecha
        ORDER BY v.idVenta ASC, p.descripcion ASC
    ";
    
    $stmtProductosLinea = $pdo->prepare($sqlProductos);
    $stmtProductosLinea->execute(['fecha' => $fecha]);
    $productosLinea = $stmtProductosLinea->fetchAll(PDO::FETCH_ASSOC);
    
    // Agrupar productos por venta
    $productosPorVenta = [];
    foreach ($productosLinea as $prod) {
        $ventaId = $prod['idVenta'];
        if (!isset($productosPorVenta[$ventaId])) {
            $productosPorVenta[$ventaId] = [];
        }
        $productosPorVenta[$ventaId][] = $prod;
    }

} catch (PDOException $e) {
    die("<div style='padding: 20px; color: red; font-family: Arial;'>
        <h3>❌ Error de Base de Datos</h3>
        <p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
    </div>");
} catch (Exception $e) {
    die("<div style='padding: 20px; color: red; font-family: Arial;'>
        <h3>❌ Error</h3>
        <p>" . htmlspecialchars($e->getMessage()) . "</p>
    </div>");
}

function formatoMoneda($valor) {
    return number_format($valor, 2, '.', ',');
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Detallado - <?php echo $fecha; ?></title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .contenedor {
            background: white;
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .encabezado {
            text-align: center;
            border-bottom: 3px solid #b8860b;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .titulo-empresa {
            font-size: 28px;
            font-weight: bold;
            color: #b8860b;
        }

        .titulo-reporte {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-top: 10px;
        }

        .fecha-reporte {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .seccion {
            margin-bottom: 35px;
        }

        .titulo-seccion {
            font-size: 14px;
            font-weight: bold;
            color: #b8860b;
            border-bottom: 2px solid #b8860b;
            padding-bottom: 8px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .resumen-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .resumen-item {
            background: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #b8860b;
            border-radius: 4px;
        }

        .resumen-label {
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .resumen-valor {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .resumen-valor-grande {
            font-size: 24px;
            color: #b8860b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }

        table thead {
            background-color: #dcdcdc;
        }

        table th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            color: #333;
            border: 1px solid #999;
        }

        table td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        table tbody tr:hover {
            background-color: #f0f0f0;
        }

        .tabla-total {
            background-color: #b8860b !important;
            color: white !important;
            font-weight: bold;
        }

        .tabla-total td {
            border-color: #b8860b;
        }

        .texto-derecha {
            text-align: right;
        }

        .texto-centro {
            text-align: center;
        }

        .encabezado-venta {
            background-color: #f5f5f5;
            padding: 10px;
            margin-top: 15px;
            margin-bottom: 8px;
            border-left: 4px solid #b8860b;
            font-weight: bold;
            font-size: 12px;
        }

        .info-venta {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            font-size: 11px;
            margin-bottom: 10px;
        }

        .info-venta-item {
            padding: 8px;
            background: #fafafa;
            border-radius: 4px;
        }

        .info-venta-label {
            color: #999;
            font-size: 9px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .info-venta-valor {
            font-weight: bold;
            color: #333;
        }

        .ganancia-positiva {
            color: #28a745;
            font-weight: bold;
        }

        .ganancia-negativa {
            color: #dc3545;
            font-weight: bold;
        }

        .botones {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 30px;
        }

        button {
            padding: 12px 25px;
            background-color: #b8860b;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }

        button:hover {
            background-color: #8b6914;
        }

        .pie {
            border-top: 2px solid #b8860b;
            padding-top: 20px;
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }

        .fila-venta {
            background-color: #e8e8e8;
            font-weight: bold;
        }

        @media print {
            .botones { display: none; }
            body { background: white; padding: 0; }
            .contenedor { box-shadow: none; }
        }

        @media (max-width: 1024px) {
            .resumen-grid { grid-template-columns: repeat(2, 1fr); }
            .info-venta { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<div class="contenedor">

    <!-- ENCABEZADO -->
    <div class="encabezado">
        <div class="titulo-empresa">JOYERIA CHABELITA</div>
        <div class="titulo-reporte">REPORTE DETALLADO DE CORTE DE CAJA</div>
        <div class="fecha-reporte">
            Fecha: <?php echo $fecha; ?> | Generado: <?php echo date('d/m/Y H:i:s'); ?>
        </div>
    </div>

    <!-- RESUMEN EJECUTIVO -->
    <div class="seccion">
        <div class="titulo-seccion">Resumen Ejecutivo</div>
        <div class="resumen-grid">
            <div class="resumen-item">
                <div class="resumen-label">Total del Día</div>
                <div class="resumen-valor resumen-valor-grande">$<?php echo formatoMoneda($totalDia); ?></div>
            </div>
            <div class="resumen-item">
                <div class="resumen-label">Ventas</div>
                <div class="resumen-valor"><?php echo $ventasRealizadas; ?></div>
            </div>
            <div class="resumen-item">
                <div class="resumen-label">Productos</div>
                <div class="resumen-valor"><?php echo $productosVendidos; ?></div>
            </div>
            <div class="resumen-item">
                <div class="resumen-label">Empleados</div>
                <div class="resumen-valor"><?php echo $empleadosActivos; ?></div>
            </div>
        </div>
    </div>

    <!-- ANÁLISIS DE GANANCIAS -->
    <div class="seccion">
        <div class="titulo-seccion">Análisis de Ganancias</div>
        <div class="resumen-grid">
            <div class="resumen-item">
                <div class="resumen-label">Total de Ventas</div>
                <div class="resumen-valor">$<?php echo formatoMoneda($totalVentas); ?></div>
            </div>
            <div class="resumen-item">
                <div class="resumen-label">Total de Costos</div>
                <div class="resumen-valor">$<?php echo formatoMoneda($totalCostos); ?></div>
            </div>
            <div class="resumen-item">
                <div class="resumen-label ganancia-positiva">Ganancia Neta</div>
                <div class="resumen-valor ganancia-positiva">$<?php echo formatoMoneda($gananciaTotal); ?></div>
            </div>
            <div class="resumen-item">
                <div class="resumen-label">Margen de Ganancia</div>
                <div class="resumen-valor"><?php echo number_format($margenGanancia, 1); ?>%</div>
            </div>
        </div>
    </div>

    <!-- DESGLOSE POR MÉTODO DE PAGO -->
    <div class="seccion">
        <div class="titulo-seccion">Desglose de Ingresos</div>
        <table>
            <thead>
                <tr>
                    <th>Método de Pago</th>
                    <th class="texto-centro">Transacciones</th>
                    <th class="texto-derecha">Importe</th>
                    <th class="texto-derecha">% Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Efectivo</td>
                    <td class="texto-centro"><?php echo $efectivoCount; ?></td>
                    <td class="texto-derecha">$<?php echo formatoMoneda($efectivo); ?></td>
                    <td class="texto-derecha"><?php echo $totalDia > 0 ? number_format(($efectivo / $totalDia) * 100, 1) : 0; ?>%</td>
                </tr>
                <tr>
                    <td>Tarjeta</td>
                    <td class="texto-centro"><?php echo $tarjetaCount; ?></td>
                    <td class="texto-derecha">$<?php echo formatoMoneda($tarjeta); ?></td>
                    <td class="texto-derecha"><?php echo $totalDia > 0 ? number_format(($tarjeta / $totalDia) * 100, 1) : 0; ?>%</td>
                </tr>
                <tr class="tabla-total">
                    <td>TOTAL</td>
                    <td class="texto-centro"><?php echo $ventasRealizadas; ?></td>
                    <td class="texto-derecha">$<?php echo formatoMoneda($totalDia); ?></td>
                    <td class="texto-derecha">100%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- TOP VENDEDORES -->
    <?php if (count($topVendedores) > 0): ?>
    <div class="seccion">
        <div class="titulo-seccion">Top 5 Vendedores</div>
        <table>
            <thead>
                <tr>
                    <th>Vendedor</th>
                    <th class="texto-centro">Ventas</th>
                    <th class="texto-derecha">Total Vendido</th>
                    <th class="texto-derecha">Promedio/Venta</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topVendedores as $vendedor): ?>
                <tr>
                    <td><?php echo htmlspecialchars($vendedor['nombre'] . ' ' . $vendedor['apellidoPaterno']); ?></td>
                    <td class="texto-centro"><?php echo $vendedor['ventasRealizadas']; ?></td>
                    <td class="texto-derecha">$<?php echo formatoMoneda($vendedor['totalVendido']); ?></td>
                    <td class="texto-derecha">$<?php echo formatoMoneda($vendedor['totalVendido'] / max($vendedor['ventasRealizadas'], 1)); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- PRODUCTOS MÁS VENDIDOS -->
    <?php if (count($topProductos) > 0): ?>
    <div class="seccion">
        <div class="titulo-seccion">Productos Más Vendidos</div>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th class="texto-centro">Cantidad</th>
                    <th class="texto-derecha">Total Vendido</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topProductos as $producto): ?>
                <tr>
                    <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                    <td><?php echo htmlspecialchars($producto['categoria'] ?? 'N/A'); ?></td>
                    <td class="texto-centro"><?php echo $producto['cantidadVendida']; ?></td>
                    <td class="texto-derecha">$<?php echo formatoMoneda($producto['totalVendido']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- DETALLE DE VENTAS -->
    <div class="seccion">
        <div class="titulo-seccion">Detalle de Ventas</div>
        
        <?php if (count($ventasDetalle) > 0): ?>
            <?php foreach ($ventasDetalle as $venta): ?>
                
                <div class="encabezado-venta">
                    Venta #<?php echo $venta['idVenta']; ?> - <?php echo htmlspecialchars($venta['clienteNombre']); ?>
                </div>

                <div class="info-venta">
                    <div class="info-venta-item">
                        <div class="info-venta-label">Fecha</div>
                        <div class="info-venta-valor"><?php echo date('d/m/Y', strtotime($venta['fechaVenta'])); ?></div>
                    </div>
                    <div class="info-venta-item">
                        <div class="info-venta-label">Vendedor</div>
                        <div class="info-venta-valor"><?php echo htmlspecialchars($venta['empleadoNombre']); ?></div>
                    </div>
                    <div class="info-venta-item">
                        <div class="info-venta-label">Tipo de Cliente</div>
                        <div class="info-venta-valor"><?php echo htmlspecialchars($venta['tipoCliente'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="info-venta-item">
                        <div class="info-venta-label">Método de Pago</div>
                        <div class="info-venta-valor"><?php echo strtoupper($venta['metodoPago']) === 'EFECTIVO' ? 'Efectivo' : 'Tarjeta'; ?></div>
                    </div>
                </div>

                <?php if (isset($productosPorVenta[$venta['idVenta']]) && count($productosPorVenta[$venta['idVenta']]) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Kilataje</th>
                                <th class="texto-centro">Cantidad</th>
                                <th class="texto-derecha">P. Venta</th>
                                <th class="texto-derecha">P. Compra</th>
                                <th class="texto-derecha">Subtotal</th>
                                <th class="texto-derecha">Ganancia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalVentaGanancia = 0;
                            $totalVentaSubtotal = 0;
                            foreach ($productosPorVenta[$venta['idVenta']] as $prod): 
                                $totalVentaGanancia += $prod['ganancia'];
                                $totalVentaSubtotal += $prod['subtotal'];
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($prod['productName']); ?></td>
                                    <td class="texto-centro"><?php echo $prod['kilataje'] ?? 'N/A'; ?></td>
                                    <td class="texto-centro"><?php echo $prod['cantidad']; ?></td>
                                    <td class="texto-derecha">$<?php echo formatoMoneda($prod['precioVenta']); ?></td>
                                    <td class="texto-derecha">$<?php echo formatoMoneda($prod['precioCompra']); ?></td>
                                    <td class="texto-derecha">$<?php echo formatoMoneda($prod['subtotal']); ?></td>
                                    <td class="texto-derecha <?php echo $prod['ganancia'] >= 0 ? 'ganancia-positiva' : 'ganancia-negativa'; ?>">
                                        $<?php echo formatoMoneda($prod['ganancia']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="fila-venta">
                                <td colspan="5" class="texto-derecha">TOTALES VENTA:</td>
                                <td class="texto-derecha">$<?php echo formatoMoneda($totalVentaSubtotal); ?></td>
                                <td class="texto-derecha <?php echo $totalVentaGanancia >= 0 ? 'ganancia-positiva' : 'ganancia-negativa'; ?>">
                                    $<?php echo formatoMoneda($totalVentaGanancia); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>

            <?php endforeach; ?>
        <?php else: ?>
            <div style="padding: 30px; text-align: center; background: #f5f5f5; border-radius: 8px;">
                <p style="color: #999;">No hay ventas registradas para esta fecha</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- PIE DE PÁGINA -->
    <div class="pie">
        <p>Reporte generado automáticamente por el Sistema de Gestión de Joyería Chabelita</p>
        <p>CONFIDENCIAL - Uso Interno</p>
        <p style="margin-top: 10px; font-size: 9px;">
            <?php echo date('d/m/Y H:i:s'); ?> | Sistema v1.0
        </p>
    </div>

</div>

<!-- BOTONES DE ACCIÓN -->
<div class="botones">
    <button onclick="window.print()">Imprimir / Guardar como PDF</button>
    <button onclick="window.close()">Cerrar</button>
</div>

</body>
</html>