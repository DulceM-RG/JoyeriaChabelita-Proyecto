<?php
session_start();
header('Content-Type: application/json');
require_once 'connection.php';

error_log("=== INICIO buscarProducto.php ===");

try {
    // Verificar autenticación
    if (!isset($_SESSION['usuario'])) {
        error_log("❌ Sin sesión activa");
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "error" => "No hay sesión activa."
        ]);
        exit;
    }
    
    // Recibir código de producto
    $entrada = json_decode(file_get_contents('php://input'), true);
    
    error_log("Datos recibidos: " . print_r($entrada, true));
    
    $codigoProducto = $entrada['codigoProducto'] ?? '';
    
    if (empty($codigoProducto)) {
        error_log("❌ Código de producto vacío");
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Debe proporcionar un código de producto."
        ]);
        exit;
    }
    
    error_log("Buscando producto con código: " . $codigoProducto);
    
    $conn = ConexionDB::setConnection();
    
    // Buscar producto por código (búsqueda parcial)
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
                    ORDER BY p.idProducto ASC
                    LIMIT 20";
    
    $stmtProducto = $conn->prepare($sqlProducto);
    $terminoBusqueda = '%' . $codigoProducto . '%';
    $stmtProducto->execute([':codigo' => $terminoBusqueda]);
    $productos = $stmtProducto->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Productos encontrados: " . count($productos));
    
    if (empty($productos)) {
        error_log("❌ No se encontraron productos");
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "error" => "No se encontraron productos con ese código.",
            "productos" => [],
            "busqueda" => $codigoProducto
        ]);
        exit;
    }
    
    // Formatear datos
    foreach ($productos as &$prod) {
        $prod['precioUnitario'] = floatval($prod['precioUnitario']);
        $prod['gramos'] = floatval($prod['gramos']);
        $prod['stock'] = intval($prod['stock']);
    }
    
    error_log("✅ Búsqueda exitosa");
    
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "productos" => $productos,
        "cantidad" => count($productos)
    ]);
    
} catch (PDOException $e) {
    error_log("❌ Error PDO: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error de base de datos: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("❌ Error general: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error del servidor: " . $e->getMessage()
    ]);
}

error_log("=== FIN buscarProducto.php ===");
?>