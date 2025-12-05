<?php
/**
 * ARCHIVO DE DEBUG PARA REPORTE DE CAJA
 * Ejecuta esto para ver qué está pasando
 */

header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 DEBUG REPORTE DE CAJA</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    h2 { color: #c9a961; border-bottom: 2px solid #c9a961; padding-bottom: 10px; margin-top: 30px; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #c9a961; color: white; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 10px 0; }
</style>";

try {
    require_once 'connection.php';
    $pdo = ConexionDB::setConnection();
    
    echo "<div class='info'>✅ Conexión a BD exitosa</div>";
    
    // Obtener fecha del parámetro o usar hoy
    $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
    echo "<div class='info'>📅 Fecha buscada: <strong>$fecha</strong></div>";
    
    // ==================== 1. VERIFICAR TABLA VENTA ====================
    echo "<h2>1️⃣ Verificar tabla VENTA</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM venta");
    $totalVentas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p>Total de ventas en la BD: <span class='success'>$totalVentas</span></p>";
    
    if ($totalVentas == 0) {
        echo "<p class='error'>❌ NO HAY VENTAS EN LA BASE DE DATOS</p>";
        echo "<p>Necesitas insertar datos de prueba. ¿Quieres que te genere un INSERT?</p>";
    } else {
        // Mostrar todas las ventas
        $stmt = $pdo->query("SELECT * FROM venta ORDER BY fechaVenta DESC LIMIT 10");
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<thead><tr><th>idVenta</th><th>fechaVenta</th><th>idEmpleado</th><th>idCliente</th><th>idIngreso</th></tr></thead>";
        echo "<tbody>";
        foreach ($ventas as $v) {
            echo "<tr>";
            echo "<td>{$v['idVenta']}</td>";
            echo "<td>{$v['fechaVenta']}</td>";
            echo "<td>{$v['idEmpleado']}</td>";
            echo "<td>{$v['idCliente']}</td>";
            echo "<td>{$v['idIngreso']}</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    }
    
    // ==================== 2. VERIFICAR VENTAS DE LA FECHA ESPECÍFICA ====================
    echo "<h2>2️⃣ Verificar ventas de la fecha: $fecha</h2>";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM venta WHERE DATE(fechaVenta) = ?");
    $stmt->execute([$fecha]);
    $ventasFecha = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($ventasFecha == 0) {
        echo "<p class='error'>❌ NO HAY VENTAS PARA LA FECHA: $fecha</p>";
        
        // Mostrar fechas disponibles
        $stmt = $pdo->query("SELECT DISTINCT DATE(fechaVenta) as fecha FROM venta ORDER BY fecha DESC");
        $fechasDisponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($fechasDisponibles) > 0) {
            echo "<p>📅 Fechas disponibles con ventas:</p>";
            echo "<ul>";
            foreach ($fechasDisponibles as $f) {
                echo "<li><a href='?fecha=$f'>$f</a></li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p class='success'>✅ Hay $ventasFecha venta(s) para la fecha $fecha</p>";
        
        // Mostrar detalles
        $stmt = $pdo->prepare("
            SELECT 
                v.idVenta,
                v.fechaVenta,
                v.idEmpleado,
                v.idCliente,
                v.idIngreso
            FROM venta v
            WHERE DATE(v.fechaVenta) = ?
        ");
        $stmt->execute([$fecha]);
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<thead><tr><th>idVenta</th><th>fechaVenta</th><th>idEmpleado</th><th>idCliente</th><th>idIngreso</th></tr></thead>";
        echo "<tbody>";
        foreach ($ventas as $v) {
            echo "<tr>";
            echo "<td>{$v['idVenta']}</td>";
            echo "<td>{$v['fechaVenta']}</td>";
            echo "<td>{$v['idEmpleado']}</td>";
            echo "<td>{$v['idCliente']}</td>";
            echo "<td>{$v['idIngreso']}</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    }
    
    // ==================== 3. VERIFICAR TABLA INGRESO ====================
    echo "<h2>3️⃣ Verificar tabla INGRESO</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM ingreso");
    $totalIngresos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p>Total de ingresos: <span class='success'>$totalIngresos</span></p>";
    
    if ($totalIngresos > 0) {
        $stmt = $pdo->query("SELECT * FROM ingreso LIMIT 10");
        $ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<thead><tr><th>idIngreso</th><th>fecha</th><th>importeTotal</th><th>metodoPago</th></tr></thead>";
        echo "<tbody>";
        foreach ($ingresos as $i) {
            echo "<tr>";
            echo "<td>{$i['idIngreso']}</td>";
            echo "<td>{$i['fecha']}</td>";
            echo "<td>\${$i['importeTotal']}</td>";
            echo "<td>{$i['metodoPago']}</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    }
    
    // ==================== 4. VERIFICAR JOIN VENTA-INGRESO ====================
    echo "<h2>4️⃣ Verificar JOIN venta → ingreso</h2>";
    
    $stmt = $pdo->prepare("
        SELECT 
            v.idVenta,
            v.fechaVenta,
            v.idIngreso,
            i.importeTotal,
            i.metodoPago
        FROM venta v
        INNER JOIN ingreso i ON v.idIngreso = i.idIngreso
        WHERE DATE(v.fechaVenta) = ?
    ");
    $stmt->execute([$fecha]);
    $ventasIngreso = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($ventasIngreso) == 0) {
        echo "<p class='error'>❌ NO HAY DATOS CON EL JOIN venta-ingreso para la fecha $fecha</p>";
        echo "<p>Posibles causas:</p>";
        echo "<ul>";
        echo "<li>Las ventas no tienen idIngreso asignado (NULL)</li>";
        echo "<li>Los idIngreso en venta no existen en la tabla ingreso</li>";
        echo "</ul>";
    } else {
        echo "<p class='success'>✅ JOIN exitoso. Se encontraron " . count($ventasIngreso) . " registro(s)</p>";
        
        echo "<table>";
        echo "<thead><tr><th>idVenta</th><th>fechaVenta</th><th>idIngreso</th><th>importeTotal</th><th>metodoPago</th></tr></thead>";
        echo "<tbody>";
        foreach ($ventasIngreso as $vi) {
            echo "<tr>";
            echo "<td>{$vi['idVenta']}</td>";
            echo "<td>{$vi['fechaVenta']}</td>";
            echo "<td>{$vi['idIngreso']}</td>";
            echo "<td>\${$vi['importeTotal']}</td>";
            echo "<td>{$vi['metodoPago']}</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    }
    
    // ==================== 5. VERIFICAR PRODUCTOS DE VENTA ====================
    echo "<h2>5️⃣ Verificar productos vendidos</h2>";
    
    $stmt = $pdo->prepare("
        SELECT 
            pv.*,
            p.descripcion
        FROM productoventa pv
        INNER JOIN venta v ON pv.idVenta = v.idVenta
        INNER JOIN producto p ON pv.idProducto = p.idProducto
        WHERE DATE(v.fechaVenta) = ?
    ");
    $stmt->execute([$fecha]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($productos) == 0) {
        echo "<p class='error'>❌ NO HAY PRODUCTOS VENDIDOS para la fecha $fecha</p>";
    } else {
        echo "<p class='success'>✅ Se encontraron " . count($productos) . " producto(s) vendido(s)</p>";
        
        echo "<table>";
        echo "<thead><tr><th>idVenta</th><th>idProducto</th><th>Descripción</th><th>Cantidad</th><th>Costo</th><th>Importe</th></tr></thead>";
        echo "<tbody>";
        foreach ($productos as $p) {
            echo "<tr>";
            echo "<td>{$p['idVenta']}</td>";
            echo "<td>{$p['idProducto']}</td>";
            echo "<td>{$p['descripcion']}</td>";
            echo "<td>{$p['cantidad']}</td>";
            echo "<td>\${$p['costo']}</td>";
            echo "<td>\${$p['importe']}</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    }
    
    // ==================== 6. RESUMEN FINAL ====================
    echo "<h2>📊 RESUMEN</h2>";
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(v.idVenta) as totalVentas,
            COALESCE(SUM(i.importeTotal), 0) as totalIngresos
        FROM venta v
        INNER JOIN ingreso i ON v.idIngreso = i.idIngreso
        WHERE DATE(v.fechaVenta) = ?
    ");
    $stmt->execute([$fecha]);
    $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<div class='info'>";
    echo "<p><strong>Fecha analizada:</strong> $fecha</p>";
    echo "<p><strong>Ventas encontradas:</strong> {$resumen['totalVentas']}</p>";
    echo "<p><strong>Total de ingresos:</strong> \$" . number_format($resumen['totalIngresos'], 2) . "</p>";
    echo "</div>";
    
    if ($resumen['totalVentas'] == 0) {
        echo "<h3 style='color: red;'>⚠️ CONCLUSIÓN</h3>";
        echo "<p>No hay datos para mostrar en el reporte porque:</p>";
        echo "<ol>";
        echo "<li>No hay ventas registradas para la fecha <strong>$fecha</strong></li>";
        echo "<li>Prueba con otra fecha usando: <code>?fecha=2024-11-01</code></li>";
        echo "</ol>";
    } else {
        echo "<h3 style='color: green;'>✅ TODO CORRECTO</h3>";
        echo "<p>Los datos existen. El reporte debería funcionar correctamente.</p>";
        echo "<p><a href='reporteCaja.php?fecha=$fecha' target='_blank' style='padding: 10px 20px; background: #c9a961; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;'>Ver Reporte Completo</a></p>";
    }
    
} catch (PDOException $e) {
    echo "<div style='padding: 20px; background: #ffebee; border-left: 4px solid #f44336; margin: 20px 0;'>";
    echo "<h3 style='color: #f44336;'>❌ Error de Conexión</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='padding: 20px; background: #ffebee; border-left: 4px solid #f44336; margin: 20px 0;'>";
    echo "<h3 style='color: #f44336;'>❌ Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>