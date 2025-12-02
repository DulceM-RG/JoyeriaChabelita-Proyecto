<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir archivo de conexión
require_once 'connection.php';

try {
    $conn = ConexionDB::setConnection();
    
    // ============================================
    // ENDPOINT: Obtener todos los proveedores (con búsqueda opcional)
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['verificarProductos'])) {
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
        
        if ($busqueda !== '') {
            // Búsqueda por RFC, Razón Social o Teléfono
            $sql = "SELECT rfc, razonSocial, telefono 
                    FROM proveedor 
                    WHERE rfc LIKE ? 
                       OR razonSocial LIKE ? 
                       OR telefono LIKE ?
                    ORDER BY razonSocial ASC";
            
            $busquedaParam = "%{$busqueda}%";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$busquedaParam, $busquedaParam, $busquedaParam]);
        } else {
            // Obtener todos los proveedores
            $sql = "SELECT rfc, razonSocial, telefono 
                    FROM proveedor 
                    ORDER BY razonSocial ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }
        
        $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $proveedores,
            'total' => count($proveedores)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // ============================================
    // ENDPOINT: Verificar si el proveedor tiene productos
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['verificarProductos'])) {
        $rfc = trim($_GET['verificarProductos']);
        
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM producto WHERE rfcProveedor = ?");
        $stmt->execute([$rfc]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'tieneProductos' => $resultado['total'] > 0,
            'totalProductos' => (int)$resultado['total']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // ============================================
    // ENDPOINT: Actualizar proveedor (PUT)
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar que los campos requeridos estén presentes
        if (!isset($data['rfcOriginal']) || !isset($data['rfc']) || !isset($data['razonSocial'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Faltan datos obligatorios'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $rfcOriginal = trim($data['rfcOriginal']);
        $rfcNuevo = strtoupper(trim($data['rfc']));
        $razonSocial = trim($data['razonSocial']);
        $telefono = isset($data['telefono']) && trim($data['telefono']) !== '' ? trim($data['telefono']) : null;
        
        // ============================================
        // VALIDAR SI EL RFC CAMBIÓ
        // ============================================
        $rfcCambio = ($rfcOriginal !== $rfcNuevo);
        
        if ($rfcCambio) {
            // Verificar que el proveedor original exista
            $stmt = $conn->prepare("SELECT rfc FROM proveedor WHERE rfc = ?");
            $stmt->execute([$rfcOriginal]);
            if ($stmt->rowCount() === 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El proveedor original no existe'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Verificar si tiene productos asociados
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM producto WHERE rfcProveedor = ?");
            $stmt->execute([$rfcOriginal]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado['total'] > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se puede cambiar el RFC porque el proveedor tiene ' . $resultado['total'] . ' producto(s) asociado(s). Primero debe reasignar o eliminar los productos.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Validar formato del nuevo RFC
            $longitudRfc = strlen($rfcNuevo);
            if ($longitudRfc < 12 || $longitudRfc > 13) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El RFC debe tener 12 caracteres (persona moral) o 13 caracteres (persona física)'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $patronRfc = '/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/';
            if (!preg_match($patronRfc, $rfcNuevo)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Formato de RFC inválido. Debe contener solo letras y números en el formato correcto'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Validar que el nuevo RFC no exista
            $stmt = $conn->prepare("SELECT rfc FROM proveedor WHERE rfc = ?");
            $stmt->execute([$rfcNuevo]);
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Ya existe un proveedor con el RFC: ' . $rfcNuevo
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        } else {
            // Si el RFC no cambió, solo verificar que el proveedor exista
            $stmt = $conn->prepare("SELECT rfc FROM proveedor WHERE rfc = ?");
            $stmt->execute([$rfcOriginal]);
            if ($stmt->rowCount() === 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El proveedor no existe'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        
        // ============================================
        // VALIDAR RAZÓN SOCIAL
        // ============================================
        if (strlen($razonSocial) < 3) {
            echo json_encode([
                'success' => false,
                'message' => 'La razón social debe tener al menos 3 caracteres'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (strlen($razonSocial) > 100) {
            echo json_encode([
                'success' => false,
                'message' => 'La razón social no puede exceder 100 caracteres'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,&\-]+$/', $razonSocial)) {
            echo json_encode([
                'success' => false,
                'message' => 'La razón social contiene caracteres no permitidos'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // ============================================
        // VALIDAR TELÉFONO
        // ============================================
        if ($telefono !== null) {
            if (!preg_match('/^[0-9]{10}$/', $telefono)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El teléfono debe tener exactamente 10 dígitos'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Verificar que el teléfono no esté en uso por otro proveedor
            $stmt = $conn->prepare("SELECT rfc FROM proveedor WHERE telefono = ? AND rfc != ?");
            $stmt->execute([$telefono, $rfcOriginal]);
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El teléfono ya está registrado en otro proveedor'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        
        // ============================================
        // ACTUALIZAR PROVEEDOR (puede incluir cambio de RFC)
        // ============================================
        $sql = "UPDATE proveedor 
                SET rfc = ?, razonSocial = ?, telefono = ? 
                WHERE rfc = ?";
        
        $stmt = $conn->prepare($sql);
        $resultado = $stmt->execute([$rfcNuevo, $razonSocial, $telefono, $rfcOriginal]);
        
        if ($resultado) {
            $mensaje = $rfcCambio 
                ? 'Proveedor actualizado exitosamente (RFC modificado de ' . $rfcOriginal . ' a ' . $rfcNuevo . ')'
                : 'Proveedor actualizado exitosamente';
            
            echo json_encode([
                'success' => true,
                'message' => $mensaje
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el proveedor'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
    
    // ============================================
    // ENDPOINT: Eliminar proveedor (DELETE)
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['rfc']) || trim($data['rfc']) === '') {
            echo json_encode([
                'success' => false,
                'message' => 'El RFC es obligatorio'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $rfc = trim($data['rfc']);
        
        // Verificar que el proveedor exista
        $stmt = $conn->prepare("SELECT rfc FROM proveedor WHERE rfc = ?");
        $stmt->execute([$rfc]);
        if ($stmt->rowCount() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'El proveedor no existe'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Verificar si el proveedor tiene productos asociados
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM producto WHERE rfcProveedor = ?");
        $stmt->execute([$rfc]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['total'] > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'No se puede eliminar el proveedor porque tiene ' . $resultado['total'] . ' producto(s) asociado(s)'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Eliminar proveedor
        $sql = "DELETE FROM proveedor WHERE rfc = ?";
        $stmt = $conn->prepare($sql);
        $resultado = $stmt->execute([$rfc]);
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Proveedor eliminado exitosamente'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar el proveedor'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
    
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode([
            'success' => false,
            'message' => 'El RFC o teléfono ya están registrados'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error de base de datos: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>