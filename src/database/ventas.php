<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'connection.php';

try {
    $pdo = ConexionDB::setConnection();
    
    if (isset($_GET['action']) && $_GET['action'] === 'obtenerVentasDelDia') {
        
        // Obtener la fecha del parámetro o usar la fecha actual
        $fechaHoy = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
        
        // Consulta para obtener las ventas del día con información del cliente y productos
        $stmt = $pdo->prepare("
            SELECT 
                v.idVenta,
                v.fechaVenta as fecha,
                CONCAT(
                    COALESCE(c.nombre, ''),
                    ' ',
                    COALESCE(c.apellidoPaterno, ''),
                    ' ',
                    COALESCE(c.apellidoMaterno, '')
                ) as cliente,
                GROUP_CONCAT(
                    CONCAT(p.descripcion, ' (', pv.cantidad, ')')
                    SEPARATOR ', '
                ) as productos,
                i.importeTotal as total
            FROM venta v
            LEFT JOIN cliente c ON v.idCliente = c.idCliente
            LEFT JOIN productoVenta pv ON v.idVenta = pv.idVenta
            LEFT JOIN producto p ON pv.idProducto = p.idProducto
            LEFT JOIN ingreso i ON v.IdIngreso = i.idIngreso
            WHERE v.fechaVenta = :fecha
            GROUP BY v.idVenta, v.fechaVenta, c.nombre, c.apellidoPaterno, c.apellidoMaterno, i.importeTotal
            ORDER BY v.idVenta DESC
        ");
        
        $stmt->execute(['fecha' => $fechaHoy]);
        $ventas = $stmt->fetchAll();
        
        // Limpiar datos de cliente (eliminar NULL)
        foreach ($ventas as &$venta) {
            $venta['cliente'] = trim($venta['cliente']);
            if (empty($venta['cliente']) || $venta['cliente'] === 'NULL NULL NULL') {
                $venta['cliente'] = 'Cliente general';
            }
            
            if (empty($venta['productos'])) {
                $venta['productos'] = 'Sin productos';
            }
            
            $venta['total'] = floatval($venta['total']);
        }
        
        echo json_encode([
            'success' => true,
            'ventas' => $ventas
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