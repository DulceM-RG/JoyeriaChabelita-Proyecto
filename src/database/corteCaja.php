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
    
    if (isset($data['action']) && $data['action'] === 'cerrarDia') {
        
        // Obtener la fecha del parámetro o usar la fecha actual
        $fechaHoy = isset($data['fecha']) ? $data['fecha'] : date('Y-m-d');
        
        // Verificar si ya se cerró el día
        $stmtVerificar = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM corte_caja
            WHERE fecha = :fecha
        ");
        
        // Intentar verificar si existe la tabla corte_caja
        try {
            $stmtVerificar->execute(['fecha' => $fechaHoy]);
            $resultado = $stmtVerificar->fetch();
            
            if ($resultado['total'] > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El día ya fue cerrado anteriormente'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        } catch (PDOException $e) {
            // Si la tabla no existe, la creamos
            if ($e->getCode() == '42S02') { // Table doesn't exist
                $createTable = "
                    CREATE TABLE IF NOT EXISTS corte_caja (
                        idCorte INT AUTO_INCREMENT PRIMARY KEY,
                        fecha DATE NOT NULL,
                        totalDia DECIMAL(10,2) NOT NULL,
                        efectivo DECIMAL(10,2) NOT NULL,
                        tarjeta DECIMAL(10,2) NOT NULL,
                        ventasRealizadas INT NOT NULL,
                        productosVendidos INT NOT NULL,
                        empleadosActivos INT NOT NULL,
                        fechaCierre DATETIME NOT NULL,
                        UNIQUE KEY fecha_unica (fecha)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ";
                $pdo->exec($createTable);
            }
        }
        
        // Obtener los datos del resumen
        $stmtTotal = $pdo->prepare("
            SELECT COALESCE(SUM(importeTotal), 0) as totalDia
            FROM ingreso
            WHERE fecha = :fecha
        ");
        $stmtTotal->execute(['fecha' => $fechaHoy]);
        $totalDia = $stmtTotal->fetch()['totalDia'];
        
        $stmtEfectivo = $pdo->prepare("
            SELECT COALESCE(SUM(importeTotal), 0) as efectivo
            FROM ingreso
            WHERE fecha = :fecha AND metodoPago = 'efectivo'
        ");
        $stmtEfectivo->execute(['fecha' => $fechaHoy]);
        $efectivo = $stmtEfectivo->fetch()['efectivo'];
        
        $stmtTarjeta = $pdo->prepare("
            SELECT COALESCE(SUM(importeTotal), 0) as tarjeta
            FROM ingreso
            WHERE fecha = :fecha AND metodoPago = 'tarjeta'
        ");
        $stmtTarjeta->execute(['fecha' => $fechaHoy]);
        $tarjeta = $stmtTarjeta->fetch()['tarjeta'];
        
        $stmtVentas = $pdo->prepare("
            SELECT COUNT(*) as ventasRealizadas
            FROM venta
            WHERE fechaVenta = :fecha
        ");
        $stmtVentas->execute(['fecha' => $fechaHoy]);
        $ventasRealizadas = $stmtVentas->fetch()['ventasRealizadas'];
        
        $stmtProductos = $pdo->prepare("
            SELECT COALESCE(SUM(pv.cantidad), 0) as productosVendidos
            FROM productoVenta pv
            INNER JOIN venta v ON pv.idVenta = v.idVenta
            WHERE v.fechaVenta = :fecha
        ");
        $stmtProductos->execute(['fecha' => $fechaHoy]);
        $productosVendidos = $stmtProductos->fetch()['productosVendidos'];
        
        $stmtEmpleados = $pdo->prepare("
            SELECT COUNT(DISTINCT idEmpleado) as empleadosActivos
            FROM venta
            WHERE fechaVenta = :fecha
        ");
        $stmtEmpleados->execute(['fecha' => $fechaHoy]);
        $empleadosActivos = $stmtEmpleados->fetch()['empleadosActivos'];
        
        // Insertar el corte de caja
        $stmtInsert = $pdo->prepare("
            INSERT INTO corte_caja 
            (fecha, totalDia, efectivo, tarjeta, ventasRealizadas, productosVendidos, empleadosActivos, fechaCierre)
            VALUES 
            (:fecha, :totalDia, :efectivo, :tarjeta, :ventasRealizadas, :productosVendidos, :empleadosActivos, NOW())
        ");
        
        $stmtInsert->execute([
            'fecha' => $fechaHoy,
            'totalDia' => $totalDia,
            'efectivo' => $efectivo,
            'tarjeta' => $tarjeta,
            'ventasRealizadas' => $ventasRealizadas,
            'productosVendidos' => $productosVendidos,
            'empleadosActivos' => $empleadosActivos
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'El día se cerró correctamente',
            'corte' => [
                'fecha' => $fechaHoy,
                'totalDia' => floatval($totalDia),
                'efectivo' => floatval($efectivo),
                'tarjeta' => floatval($tarjeta),
                'ventasRealizadas' => intval($ventasRealizadas),
                'productosVendidos' => intval($productosVendidos),
                'empleadosActivos' => intval($empleadosActivos)
            ]
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