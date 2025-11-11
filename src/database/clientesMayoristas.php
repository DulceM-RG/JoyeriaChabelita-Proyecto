<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'connection.php';

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Determinar la acción solicitada
    $action = '';
    $entrada = [];
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        $entrada = $_GET;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verificar el Content-Type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            // Datos enviados como JSON
            $raw_input = file_get_contents('php://input');
            $entrada = json_decode($raw_input, true);
            
            if ($entrada === null && json_last_error() !== JSON_ERROR_NONE) {
                enviarRespuesta(400, false, "Datos JSON inválidos");
            }
        } else {
            // Datos enviados como FormData
            $entrada = $_POST;
        }
        
        $action = $entrada['action'] ?? '';
    }

    // Validar que hay una acción
    if (empty($action)) {
        enviarRespuesta(400, false, 'No se especificó ninguna acción');
    }

    // Establecer conexión con la base de datos
    $conn = ConexionDB::setConnection();

    // Manejar diferentes acciones
    switch ($action) {
        case 'obtenerClientes':
            obtenerClientes($conn);
            break;
            
        case 'actualizarCliente':
            actualizarCliente($conn, $entrada);
            break;
            
        case 'eliminarCliente':
            eliminarCliente($conn, $entrada);
            break;
            
        default:
            enviarRespuesta(400, false, "Acción no válida: '$action'");
    }

} catch (PDOException $e) {
    error_log("Error PDO en clientesMayoristas.php: " . $e->getMessage());
    enviarRespuesta(500, false, 'Error del servidor al procesar la solicitud');
} catch (Exception $e) {
    error_log("Error inesperado en clientesMayoristas.php: " . $e->getMessage());
    enviarRespuesta(500, false, 'Error inesperado en el servidor');
}

// ============ FUNCIONES AUXILIARES ============

/**
 * Función helper para enviar respuestas JSON
 */
function enviarRespuesta($httpCode, $success, $message, $data = null) {
    http_response_code($httpCode);
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Validar formato de teléfono (10 dígitos)
 */
function validarTelefono($telefono) {
    return preg_match('/^\d{10}$/', $telefono);
}

/**
 * Limpiar y normalizar datos de entrada
 */
function limpiarDatos($data) {
    return array_map(function($value) {
        return is_string($value) ? trim($value) : $value;
    }, $data);
}

// ============ FUNCIONES PRINCIPALES ============

/**
 * Obtener todos los clientes mayoristas (idTipoCliente = 2)
 */
function obtenerClientes($conn) {
    try {
        $sql = "SELECT 
                    idCliente, 
                    idTipoCliente, 
                    nombre, 
                    apellidoPaterno, 
                    apellidoMaterno, 
                    telefono 
                FROM cliente 
                WHERE idTipoCliente = 2 
                ORDER BY idCliente ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Normalizar valores NULL
        foreach ($clientes as &$cliente) {
            $cliente['nombre'] = $cliente['nombre'] ?? '';
            $cliente['apellidoPaterno'] = $cliente['apellidoPaterno'] ?? '';
            $cliente['apellidoMaterno'] = $cliente['apellidoMaterno'] ?? '';
            $cliente['telefono'] = $cliente['telefono'] ?? '';
        }

        enviarRespuesta(200, true, 'Clientes obtenidos exitosamente', [
            'clientes' => $clientes,
            'total' => count($clientes)
        ]);

    } catch (PDOException $e) {
        error_log("Error al obtener clientes: " . $e->getMessage());
        enviarRespuesta(500, false, 'Error al obtener los clientes de la base de datos');
    }
}

/**
 * Actualizar datos de un cliente
 */
function actualizarCliente($conn, $data) {
    try {
        // Limpiar datos de entrada
        $data = limpiarDatos($data);

        // Validaciones
        if (empty($data['idCliente'])) {
            enviarRespuesta(400, false, 'ID de cliente no proporcionado');
        }

        if (empty($data['nombre'])) {
            enviarRespuesta(400, false, 'El nombre no puede estar vacío');
        }

        if (empty($data['apellidoPaterno'])) {
            enviarRespuesta(400, false, 'El apellido paterno no puede estar vacío');
        }

        if (empty($data['telefono'])) {
            enviarRespuesta(400, false, 'El teléfono no puede estar vacío');
        }

        if (!validarTelefono($data['telefono'])) {
            enviarRespuesta(400, false, 'El teléfono debe tener exactamente 10 dígitos numéricos');
        }

        // Iniciar transacción
        $conn->beginTransaction();

        // Verificar que el cliente existe y es mayorista
        $sqlCheck = "SELECT idCliente FROM cliente 
                     WHERE idCliente = :idCliente AND idTipoCliente = 2";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->execute([':idCliente' => $data['idCliente']]);
        
        if (!$stmtCheck->fetch()) {
            $conn->rollBack();
            enviarRespuesta(404, false, 'Cliente no encontrado o no es mayorista');
        }

        // Actualizar en la base de datos
        $sql = "UPDATE cliente 
                SET nombre = :nombre, 
                    apellidoPaterno = :apellidoPaterno, 
                    apellidoMaterno = :apellidoMaterno, 
                    telefono = :telefono 
                WHERE idCliente = :idCliente AND idTipoCliente = 2";
        
        $stmt = $conn->prepare($sql);
        $resultado = $stmt->execute([
            ':nombre' => $data['nombre'],
            ':apellidoPaterno' => $data['apellidoPaterno'],
            ':apellidoMaterno' => $data['apellidoMaterno'] ?? '',
            ':telefono' => $data['telefono'],
            ':idCliente' => $data['idCliente']
        ]);

        if ($resultado) {
            $conn->commit();
            enviarRespuesta(200, true, 'Cliente actualizado exitosamente', [
                'idCliente' => $data['idCliente']
            ]);
        } else {
            $conn->rollBack();
            enviarRespuesta(500, false, 'No se pudo actualizar el cliente');
        }

    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error al actualizar cliente: " . $e->getMessage());
        enviarRespuesta(500, false, 'Error al actualizar el cliente en la base de datos');
    }
}

/**
 * Eliminar un cliente
 */
function eliminarCliente($conn, $data) {
    try {
        // Validar ID
        if (empty($data['idCliente'])) {
            enviarRespuesta(400, false, 'ID de cliente no proporcionado');
        }

        // Iniciar transacción
        $conn->beginTransaction();

        // Verificar existencia del cliente
        $sqlCheck = "SELECT idCliente, nombre, apellidoPaterno 
                     FROM cliente 
                     WHERE idCliente = :idCliente AND idTipoCliente = 2";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->execute([':idCliente' => $data['idCliente']]);
        $cliente = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            $conn->rollBack();
            enviarRespuesta(404, false, 'Cliente no encontrado o no es mayorista');
        }

        // Eliminar de la base de datos
        $sql = "DELETE FROM cliente 
                WHERE idCliente = :idCliente AND idTipoCliente = 2";
        $stmt = $conn->prepare($sql);
        $resultado = $stmt->execute([':idCliente' => $data['idCliente']]);

        if ($resultado && $stmt->rowCount() > 0) {
            $conn->commit();
            enviarRespuesta(200, true, 'Cliente eliminado exitosamente', [
                'clienteEliminado' => $cliente['nombre'] . ' ' . $cliente['apellidoPaterno']
            ]);
        } else {
            $conn->rollBack();
            enviarRespuesta(500, false, 'No se pudo eliminar el cliente');
        }

    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error al eliminar cliente: " . $e->getMessage());
        
        // Verificar si es un error de constraint (relaciones con otras tablas)
        if ($e->getCode() == '23000') {
            enviarRespuesta(409, false, 'No se puede eliminar: el cliente tiene registros asociados (ventas, pedidos, etc.)');
        }
        
        enviarRespuesta(500, false, 'Error al eliminar el cliente de la base de datos');
    }
}
?>