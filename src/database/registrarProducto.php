<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir archivo de conexión
require_once 'connection.php';

try {
    // Obtener conexión usando tu clase existente
    $conn = ConexionDB::setConnection();
    
    // ============================================
    // ENDPOINT: Obtener proveedores
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'proveedores') {
        $stmt = $conn->prepare("SELECT rfc, razonSocial, telefono FROM proveedor ORDER BY razonSocial ASC");
        $stmt->execute();
        $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $proveedores
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // ============================================
    // ENDPOINT: Obtener categorías
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'categorias') {
        $stmt = $conn->prepare("SELECT idCategoria, nombre FROM categoria ORDER BY nombre ASC");
        $stmt->execute();
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $categorias
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // ============================================
    // ENDPOINT: Registrar producto
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar que todos los campos requeridos estén presentes
        $camposRequeridos = ['idProducto', 'idCategoria', 'rfcProveedor', 'stock', 'kilataje', 'descripcion', 'precioCompra', 'precioUnitario', 'gramos'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($data[$campo]) || trim($data[$campo]) === '') {
                echo json_encode([
                    'success' => false,
                    'message' => "El campo '$campo' es obligatorio"
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        
        // ============================================
        // VALIDACIONES RIGUROSAS
        // ============================================
        
        // 1. Validar idProducto (VARCHAR(20))
        $idProducto = trim($data['idProducto']);
        if (strlen($idProducto) > 20) {
            echo json_encode([
                'success' => false,
                'message' => 'El código del producto no puede exceder 20 caracteres'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $idProducto)) {
            echo json_encode([
                'success' => false,
                'message' => 'El código del producto solo puede contener letras, números, guiones y guiones bajos'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 2. Validar que el producto no exista
        $stmt = $conn->prepare("SELECT idProducto FROM producto WHERE idProducto = ?");
        $stmt->execute([$idProducto]);
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Ya existe un producto con el código: ' . $idProducto
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 3. Validar idCategoria (debe existir)
        $idCategoria = intval($data['idCategoria']);
        $stmt = $conn->prepare("SELECT idCategoria FROM categoria WHERE idCategoria = ?");
        $stmt->execute([$idCategoria]);
        if ($stmt->rowCount() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'La categoría seleccionada no existe'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 4. Validar rfcProveedor (VARCHAR(13), debe existir)
        $rfcProveedor = trim($data['rfcProveedor']);
        if (strlen($rfcProveedor) > 13) {
            echo json_encode([
                'success' => false,
                'message' => 'El RFC del proveedor no puede exceder 13 caracteres'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $stmt = $conn->prepare("SELECT rfc FROM proveedor WHERE rfc = ?");
        $stmt->execute([$rfcProveedor]);
        if ($stmt->rowCount() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'El proveedor seleccionado no existe'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 5. Validar stock (INT, debe ser positivo)
        $stock = intval($data['stock']);
        if ($stock < 0) {
            echo json_encode([
                'success' => false,
                'message' => 'El stock no puede ser negativo'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if ($stock > 2147483647) {
            echo json_encode([
                'success' => false,
                'message' => 'El stock excede el límite permitido'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 6. Validar kilataje (ENUM)
        $kilataje = $data['kilataje'];
        $kilatajes_validos = ['8K', '10K', '14K', '18k'];
        if (!in_array($kilataje, $kilatajes_validos)) {
            echo json_encode([
                'success' => false,
                'message' => 'Kilataje no válido. Valores permitidos: 8K, 10K, 14K, 18k'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 7. Validar descripción (VARCHAR(100))
        $descripcion = trim($data['descripcion']);
        if (strlen($descripcion) > 100) {
            echo json_encode([
                'success' => false,
                'message' => 'La descripción no puede exceder 100 caracteres. Actualmente tiene: ' . strlen($descripcion)
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (strlen($descripcion) < 3) {
            echo json_encode([
                'success' => false,
                'message' => 'La descripción debe tener al menos 3 caracteres'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 8. Validar precioCompra (DECIMAL(8,2))
        $precioCompra = floatval($data['precioCompra']);
        if ($precioCompra <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'El precio de compra debe ser mayor a 0'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if ($precioCompra > 999999.99) {
            echo json_encode([
                'success' => false,
                'message' => 'El precio de compra excede el límite permitido (máximo: $999,999.99)'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (round($precioCompra, 2) != $precioCompra) {
            echo json_encode([
                'success' => false,
                'message' => 'El precio de compra solo puede tener hasta 2 decimales'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 9. Validar precioUnitario (DECIMAL(8,2))
        $precioUnitario = floatval($data['precioUnitario']);
        if ($precioUnitario <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'El precio de venta debe ser mayor a 0'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if ($precioUnitario > 999999.99) {
            echo json_encode([
                'success' => false,
                'message' => 'El precio de venta excede el límite permitido (máximo: $999,999.99)'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        // Validar que tenga máximo 2 decimales
        if (round($precioUnitario, 2) != $precioUnitario) {
            echo json_encode([
                'success' => false,
                'message' => 'El precio de venta solo puede tener hasta 2 decimales'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 9.1 Validar que precio de venta sea mayor que precio de compra
        if ($precioUnitario <= $precioCompra) {
            echo json_encode([
                'success' => false,
                'message' => 'El precio de venta debe ser mayor al precio de compra'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 10. Validar gramos (DECIMAL(5,2))
        $gramos = floatval($data['gramos']);
        if ($gramos <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Los gramos deben ser mayor a 0'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if ($gramos > 999.99) {
            echo json_encode([
                'success' => false,
                'message' => 'Los gramos exceden el límite permitido (máximo: 999.99 gr)'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        // Validar que tenga máximo 2 decimales
        if (round($gramos, 2) != $gramos) {
            echo json_encode([
                'success' => false,
                'message' => 'Los gramos solo pueden tener hasta 2 decimales'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // ============================================
        // INSERTAR PRODUCTO EN LA BASE DE DATOS
        // ============================================
        $sql = "INSERT INTO producto (idProducto, idCategoria, rfcProveedor, stock, kilataje, descripcion, precioCompra, precioUnitario, gramos) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $resultado = $stmt->execute([
            $idProducto,
            $idCategoria,
            $rfcProveedor,
            $stock,
            $kilataje,
            $descripcion,
            $precioCompra,
            $precioUnitario,
            $gramos
        ]);
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Producto registrado exitosamente',
                'data' => [
                    'idProducto' => $idProducto
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al registrar el producto'
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>