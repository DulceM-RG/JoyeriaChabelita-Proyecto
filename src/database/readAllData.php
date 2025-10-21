<?php
// readAllData.php - VERSIÓN CORREGIDA PARA CREDENCIALES
header('Content-Type: application/json'); 
require_once 'connection.php'; 

try {
    $conn = ConexionDB::setConnection();
    
    $entrada = json_decode(file_get_contents('php://input'), true);
    $tabla = $entrada['tabla'] ?? null; 

    // Validar tablas permitidas
   $tablasValidas = [
    'categoria',
    'producto',
    'productopedido',
    'pedido',
    'proveedor',
    'direccion',
    'empleado',
    'puestoempleado',
    'credenciales',
    'tipocliente',
    'cliente',
    'venta',
    'productoVenta',
    'ingreso'
    ];

    if (!in_array($tabla, $tablasValidas)) {
        echo json_encode(["errorDB" => "Tabla no permitida."]);
        exit;
    }

    // ✅ CASO ESPECIAL: CREDENCIALES CON JOIN
    if ($tabla === 'credenciales') {
        $sql = "SELECT 
                    c.idControl,
                    c.idEmpleado,
                    CONCAT(e.nombre, ' ', e.apellidoPaterno, ' ', IFNULL(e.apellidoMaterno, '')) as nombreCompleto,
                    c.idControl as usuario,
                    c.contrasena,
                    DATE_FORMAT(c.fechaCreacion, '%d/%m/%Y') as fechaCreacion,
                    DATE_FORMAT(c.ultimoCambio, '%d/%m/%Y') as ultimoCambio,
                    c.activo,
                    c.intentosFallidos
                FROM credenciales c
                INNER JOIN empleado e ON c.idEmpleado = e.idEmpleado
                ORDER BY c.idEmpleado";
    } else {
        // Para otras tablas
        $sql = "SELECT * FROM `$tabla`";
    }
    
    $resultado = $conn->query($sql);
    $datos = $resultado->fetchAll(PDO::FETCH_ASSOC);
    
    // Devolver los datos o un array vacío
    echo json_encode($datos ?: []); 

} catch (PDOException $e) {
    echo json_encode(["errorDB" => "Error en la consulta: " . $e->getMessage()]);
}
?>