<?php
session_start();
header('Content-Type: application/json');
require_once 'connection.php';

try {
    // Verificar autenticación
    if (!isset($_SESSION['usuario'])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "error" => "No hay sesión activa."
        ]);
        exit;
    }
    
    // Recibir código de producto
    $entrada = json_decode(file_get_contents('php://input'), true);
    $codigoProducto = $entrada['codigoProducto'] ?? '';
    
    if (empty($codigoProducto)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Debe proporcionar un código de producto."
        ]);
        exit;
    }
    
    $conn = ConexionDB::setConnection();
    
    // Buscar producto por código (puede ser búsqueda parcial)
    $sqlProducto = "SELECT 
                        p.idProducto,
                        p.stock,
                        p.kilataje,
                        p.descripcion,
                        p.precioUnitario,
                        p.gramos,
                        c.nombre as categoria
                    FROM producto p
                    INNER JOIN categoria c ON p.idCategoria = c.idCategoria
                    WHERE p.idProducto LIKE :codigo
                    AND p.stock > 0
                    LIMIT 10";
    
    $stmtProducto = $conn->prepare($sqlProducto);
    $stmtProducto->execute([':codigo' => '%' . $codigoProducto . '%']);
    $productos = $stmtProducto->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($productos)) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "error" => "No se encontraron productos con ese código.",
            "productos" => []
        ]);
        exit;
    }
    
    // Formatear precios
    foreach ($productos as &$prod) {
        $prod['precioUnitario'] = floatval($prod['precioUnitario']);
        $prod['gramos'] = floatval($prod['gramos']);
        $prod['stock'] = intval($prod['stock']);
    }
    
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "productos" => $productos,
        "cantidad" => count($productos)
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error de base de datos: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error del servidor: " . $e->getMessage()
    ]);
}
?>