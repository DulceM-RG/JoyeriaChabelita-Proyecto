<?php
//Le dice al navegador: "La respuesta que te voy a enviar está en formato JSON y usa codificación UTF-8"
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// archivo de conexión
require_once 'connection.php';

try {
    // Obtener conexión 
    $conn = ConexionDB::setConnection();
    
    // ============================================
    // ENDPOINT: Registrar proveedor
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar que todos los campos requeridos estén presentes
        $camposRequeridos = ['rfc', 'razonSocial', 'telefono'];
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
        
        // 1. Validar RFC (VARCHAR(13))
        $rfc = strtoupper(trim($data['rfc'])); // Convertir a mayúsculas
        
        // Longitud del RFC: 12 para persona moral, 13 para persona física
        $longitudRfc = strlen($rfc);
        if ($longitudRfc < 12 || $longitudRfc > 13) {
            echo json_encode([
                'success' => false,
                'message' => 'El RFC debe tener 12 caracteres (persona moral) o 13 caracteres (persona física)'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Validar formato del RFC
        // Persona Física: 4 letras + 6 números + 3 caracteres alfanuméricos
        // Persona Moral: 3 letras + 6 números + 3 caracteres alfanuméricos
        $patronRfc = '/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/';
        if (!preg_match($patronRfc, $rfc)) {
            echo json_encode([
                'success' => false,
                'message' => 'Formato de RFC inválido. Debe contener solo letras y números en el formato correcto'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Validar que el RFC no exista
        $stmt = $conn->prepare("SELECT rfc FROM proveedor WHERE rfc = ?");
        $stmt->execute([$rfc]);
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Ya existe un proveedor con el RFC: ' . $rfc
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 2. Validar Razón Social (VARCHAR(100))
        $razonSocial = trim($data['razonSocial']);
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
                'message' => 'La razón social no puede exceder 100 caracteres. Actualmente tiene: ' . strlen($razonSocial)
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Validar que contenga solo letras, números, espacios y caracteres permitidos
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,&\-]+$/', $razonSocial)) {
            echo json_encode([
                'success' => false,
                'message' => 'La razón social contiene caracteres no permitidos'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // 3. Validar Teléfono (BIGINT - 10 dígitos, opcional)
        $telefono = null;
        if (isset($data['telefono']) && trim($data['telefono']) !== '') {
            $telefonoStr = trim($data['telefono']);
            
            // Solo números
            if (!preg_match('/^[0-9]+$/', $telefonoStr)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El teléfono solo puede contener números'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Exactamente 10 dígitos
            if (strlen($telefonoStr) != 10) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El teléfono debe tener exactamente 10 dígitos'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $telefono = intval($telefonoStr);
            
            // Validar rango de BIGINT (no mayor a 9223372036854775807)
            if ($telefono > 9999999999) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El número de teléfono excede el límite permitido'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Validar que el teléfono no exista (es UNIQUE)
            $stmt = $conn->prepare("SELECT rfc FROM proveedor WHERE telefono = ?");
            $stmt->execute([$telefono]);
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Ya existe un proveedor con el teléfono: ' . $telefonoStr
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        
        // ============================================
        // INSERTAR PROVEEDOR EN LA BASE DE DATOS
        // ============================================
        $sql = "INSERT INTO proveedor (rfc, razonSocial, telefono) VALUES (?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $resultado = $stmt->execute([
            $rfc,
            $razonSocial,
            $telefono
        ]);
        
        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Proveedor registrado exitosamente',
                'data' => [
                    'rfc' => $rfc,
                    'razonSocial' => $razonSocial,
                    'telefono' => $telefono
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al registrar el proveedor'
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
} catch (PDOException $e) {
    // Manejar errores de duplicados
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