<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'connection.php';

try {
    $conn = ConexionDB::setConnection();
    
    // ============================================
    // ENDPOINT: Obtener todos los productos (con búsqueda opcional)
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['obtenerCategorias']) && !isset($_GET['obtenerProveedores'])) {
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
        
        $sql = "SELECT 
                    p.idProducto,
                    p.idCategoria,
                    c.nombre as nombreCategoria,
                    p.rfcProveedor,
                    pr.razonSocial as nombreProveedor,
                    p.stock,
                    p.kilataje,
                    p.descripcion,
                    p.precioCompra,
                    p.precioUnitario,
                    p.gramos
                FROM producto p
                LEFT JOIN categoria c ON p.idCategoria = c.idCategoria
                LEFT JOIN proveedor pr ON p.rfcProveedor = pr.rfc";
        
        if ($busqueda !== '') {
            $sql .= " WHERE p.idProducto LIKE ? 
                      OR c.nombre LIKE ? 
                      OR p.kilataje LIKE ?
                      OR p.descripcion LIKE ?
                      OR pr.razonSocial LIKE ?";
            
            $busquedaParam = "%{$busqueda}%";
            $stmt = $conn->prepare($sql . " ORDER BY p.idProducto ASC");
            $stmt->execute([$busquedaParam, $busquedaParam, $busquedaParam, $busquedaParam, $busquedaParam]);
        } else {
            $stmt = $conn->prepare($sql . " ORDER BY p.idProducto ASC");
            $stmt->execute();
        }
        
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $productos,
            'total' => count($productos)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // ============================================
    // ENDPOINT: Obtener categorías
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['obtenerCategorias'])) {
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
    // ENDPOINT: Obtener proveedores
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['obtenerProveedores'])) {
        $stmt = $conn->prepare("SELECT rfc, razonSocial FROM proveedor ORDER BY razonSocial ASC");
        $stmt->execute();
        $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $proveedores
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // ============================================
    // ENDPOINT: Actualizar producto (PUT)
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar campos requeridos
        $camposRequeridos = ['idProductoOriginal', 'idProducto', 'idCategoria', 'rfcProveedor', 'stock', 'kilataje', 'descripcion', 'precioCompra', 'precioUnitario', 'gramos'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($data[$campo]) || trim($data[$campo]) === '') {
                echo json_encode([
                    'success' => false,
                    'message' => "El campo '$campo' es obligatorio"
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        
        $idProductoOriginal = trim($data['idProductoOriginal']);
        $idProductoNuevo = trim($data['idProducto']);
        $idCategoria = intval($data['idCategoria']);
        $rfcProveedor = trim($data['rfcProveedor']);
        $stock = intval($data['stock']);
        $kilataje = $data['kilataje'];
        $descripcion = trim($data['descripcion']);
        $precioCompra = floatval($data['precioCompra']);
        $precioUnitario = floatval($data['precioUnitario']);
        $gramos = floatval($data['gramos']);
        
        // ============================================
        // VALIDACIONES
        // ============================================
        
        // 1. Verificar que el producto original exista
        $stmt = $conn->prepare("SELECT idProducto FROM producto WHERE idProducto = ?");
        $stmt->execute([$idProductoOriginal]);
        if ($stmt->rowCount() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'El producto no existe'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 2. Si cambió el código, validar que no exista el nuevo
        if ($idProductoOriginal !== $idProductoNuevo) {
            if (strlen($idProductoNuevo) > 20) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El código no puede exceder 20 caracteres'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            if (!preg_match('/^[A-Za-z0-9_-]+$/', $idProductoNuevo)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El código solo puede contener letras, números, guiones y guiones bajos'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $stmt = $conn->prepare("SELECT idProducto FROM producto WHERE idProducto = ?");
            $stmt->execute([$idProductoNuevo]);
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Ya existe un producto con el código: ' . $idProductoNuevo
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        
        // 3. Validar categoría
        $stmt = $conn->prepare("SELECT idCategoria FROM categoria WHERE idCategoria = ?");
        $stmt->execute([$idCategoria]);
        if ($stmt->rowCount() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'La categoría seleccionada no existe'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 4. Validar proveedor
        $stmt = $conn->prepare("SELECT rfc FROM proveedor WHERE rfc = ?");
        $stmt->execute([$rfcProveedor]);
        if ($stmt->rowCount() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'El proveedor seleccionado no existe'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 5. Validar stock
        if ($stock < 0) {
            echo json_encode([
                'success' => false,
                'message' => 'El stock no puede ser negativo'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 6. Validar kilataje
        $kilatajes_validos = ['8k', '10K', '14K', '18k'];
        if (!in_array($kilataje, $kilatajes_validos)) {
            echo json_encode([
                'success' => false,
                'message' => 'Kilataje no válido'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 7. Validar descripción
        if (strlen($descripcion) < 3) {
            echo json_encode([
                'success' => false,
                'message' => 'La descripción debe tener al menos 3 caracteres'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (strlen($descripcion) > 100) {
            echo json_encode([
                'success' => false,
                'message' => 'La descripción no puede exceder 100 caracteres'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 8. Validar precio de compra
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
                'message' => 'El precio de compra excede el límite permitido'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 9. Validar precio de venta
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
                'message' => 'El precio de venta excede el límite permitido'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if ($precioUnitario <= $precioCompra) {
            echo json_encode([
                'success' => false,
                'message' => 'El precio de venta debe ser mayor al precio de compra'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 10. Validar gramos
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
                'message' => 'Los gramos exceden el límite permitido'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // ============================================
        // ACTUALIZAR PRODUCTO
        // ============================================
        $sql = "UPDATE producto 
                SET idProducto = ?, idCategoria = ?, rfcProveedor = ?, 
                    stock = ?, kilataje = ?, descripcion = ?, 
                    precioCompra = ?, precioUnitario = ?, gramos = ?
                WHERE idProducto = ?";
        
        $stmt = $conn->prepare($sql);
        $resultado = $stmt->execute([
            $idProductoNuevo, $idCategoria, $rfcProveedor,
            $stock, $kilataje, $descripcion,
            $precioCompra, $precioUnitario, $gramos,
            $idProductoOriginal
        ]);
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Producto actualizado exitosamente'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el producto'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
    
    // ============================================
    // ENDPOINT: Eliminar producto (DELETE)
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['idProducto']) || trim($data['idProducto']) === '') {
            echo json_encode([
                'success' => false,
                'message' => 'El código del producto es obligatorio'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $idProducto = trim($data['idProducto']);
        
        // Verificar que el producto exista
        $stmt = $conn->prepare("SELECT idProducto FROM producto WHERE idProducto = ?");
        $stmt->execute([$idProducto]);
        if ($stmt->rowCount() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'El producto no existe'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Eliminar producto
        $sql = "DELETE FROM producto WHERE idProducto = ?";
        $stmt = $conn->prepare($sql);
        $resultado = $stmt->execute([$idProducto]);
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar el producto'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
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