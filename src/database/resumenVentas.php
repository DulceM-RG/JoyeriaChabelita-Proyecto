<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir archivo de conexión a la base de datos
require_once __DIR__ . '/connection.php';

// Obtener la acción solicitada
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch($action) {
        case 'obtenerVentasDelDia':
            obtenerVentasDelDia();
            break;
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
            break;
    }
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}

function obtenerVentasDelDia() {
    try {
        // Establecer conexión usando PDO
        $pdo = ConexionDB::setConnection();
        
        // Obtener la fecha (si no se envía, usar la fecha de hoy)
        $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
        
        // Consulta para obtener las ventas del día con detalles
        $query = "
            SELECT 
                v.idVenta,
                DATE_FORMAT(v.fechaVenta, '%Y-%m-%d %H:%i') as fecha,
                CONCAT_WS(' ', c.nombre, c.apellidoPaterno, c.apellidoMaterno) as cliente,
                GROUP_CONCAT(p.descripcion SEPARATOR ', ') as productos,
                i.importeTotal as total
            FROM venta v
            LEFT JOIN cliente c ON v.idCliente = c.idCliente
            LEFT JOIN ingreso i ON v.idIngreso = i.idIngreso
            LEFT JOIN productoVenta pv ON v.idVenta = pv.idVenta
            LEFT JOIN producto p ON pv.idProducto = p.idProducto
            WHERE DATE(v.fechaVenta) = :fecha
            GROUP BY v.idVenta, v.fechaVenta, c.nombre, c.apellidoPaterno, c.apellidoMaterno, i.importeTotal
            ORDER BY v.fechaVenta DESC
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si no hay ventas
        if(!$ventas || count($ventas) == 0) {
            echo json_encode([
                'success' => true,
                'ventas' => [],
                'message' => 'No hay ventas para esta fecha'
            ]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'ventas' => $ventas,
            'total' => count($ventas)
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener ventas: ' . $e->getMessage()
        ]);
    }
}
?>