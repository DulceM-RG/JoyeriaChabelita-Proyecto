<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir archivo de conexión a la base de datos
require_once __DIR__ . '/connection.php';

// Obtener la acción solicitada
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Debug: descomentar para ver qué se está recibiendo
// echo json_encode(['debug' => $_GET, 'action' => $action]); exit;

try {
    switch($action) {
        case 'obtenerResumen':
            obtenerResumenDia();
            break;
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida',
                'received_action' => $action,
                'get_params' => $_GET
            ]);
            break;
    }
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}

function obtenerResumenDia() {
    try {
        // Establecer conexión usando PDO
        $pdo = ConexionDB::setConnection();
        
        // Obtener la fecha (si no se envía, usar la fecha de hoy)
        $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
        
        // Consulta para obtener el resumen del día
        $query = "
            SELECT 
                COUNT(DISTINCT v.idVenta) as ventasRealizadas,
                COALESCE(SUM(i.importeTotal), 0) as totalVendido,
                COALESCE(SUM(pv.cantidad), 0) as productosVendidos
            FROM venta v
            LEFT JOIN ingreso i ON v.idIngreso = i.idIngreso
            LEFT JOIN productoVenta pv ON v.idVenta = pv.idVenta
            WHERE DATE(v.fechaVenta) = :fecha
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si no hay datos, establecer valores en 0
        if(!$datos || $datos['ventasRealizadas'] == 0) {
            $datos = [
                'ventasRealizadas' => 0,
                'totalVendido' => 0,
                'productosVendidos' => 0
            ];
        }
        
        echo json_encode([
            'success' => true,
            'ventasRealizadas' => (int)$datos['ventasRealizadas'],
            'totalVendido' => (float)$datos['totalVendido'],
            'productosVendidos' => (int)$datos['productosVendidos'],
            'fecha' => $fecha
        ]);
        
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener resumen: ' . $e->getMessage()
        ]);
    }
}
?>