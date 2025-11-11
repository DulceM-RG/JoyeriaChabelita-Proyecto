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
    
    $entrada = json_decode(file_get_contents('php://input'), true);
    $accion = $entrada['accion'] ?? '';
    
    $conn = ConexionDB::setConnection();
    
    // ==================== BUSCAR CLIENTE ====================
    if ($accion === 'buscar') {
        $busqueda = $entrada['busqueda'] ?? '';
        
        if (empty($busqueda)) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "Debe proporcionar un término de búsqueda."
            ]);
            exit;
        }
        
        // Buscar por teléfono o nombre
        $sqlBuscar = "SELECT 
                        c.idCliente,
                        c.nombre,
                        c.apellidoPaterno,
                        c.apellidoMaterno,
                        c.telefono,
                        tc.tipo as tipoCliente,
                        c.idTipoCliente
                      FROM cliente c
                      INNER JOIN tipocliente tc ON c.idTipoCliente = tc.idTipoCliente
                      WHERE c.idTipoCliente = 2
                      AND (c.telefono LIKE :busqueda 
                           OR CONCAT(c.nombre, ' ', c.apellidoPaterno, ' ', c.apellidoMaterno) LIKE :busqueda
                           OR c.nombre LIKE :busqueda)
                      LIMIT 10";
        
        $stmtBuscar = $conn->prepare($sqlBuscar);
        $stmtBuscar->execute([':busqueda' => '%' . $busqueda . '%']);
        $clientes = $stmtBuscar->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($clientes)) {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "error" => "No se encontraron clientes con ese criterio.",
                "clientes" => []
            ]);
            exit;
        }
        
        // Formatear resultados
        foreach ($clientes as &$cliente) {
            $cliente['nombreCompleto'] = trim($cliente['nombre'] . ' ' . 
                                              $cliente['apellidoPaterno'] . ' ' . 
                                              $cliente['apellidoMaterno']);
        }
        
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "clientes" => $clientes,
            "cantidad" => count($clientes)
        ]);
        
    }
    // ==================== CREAR CLIENTE ====================
    elseif ($accion === 'crear') {
        $nombre = trim($entrada['nombre'] ?? '');
        $apellidoPaterno = trim($entrada['apellidoPaterno'] ?? '');
        $apellidoMaterno = trim($entrada['apellidoMaterno'] ?? '');
        $telefono = trim($entrada['telefono'] ?? '');
        
        // Validaciones
        if (empty($nombre) || empty($apellidoPaterno) || empty($apellidoMaterno) || empty($telefono)) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "Todos los campos son obligatorios."
            ]);
            exit;
        }
        
        // Validar teléfono (10 dígitos)
        if (!preg_match('/^[0-9]{10}$/', $telefono)) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "El teléfono debe contener exactamente 10 dígitos."
            ]);
            exit;
        }
        
        $conn->beginTransaction();
        
        // Verificar si el teléfono ya existe
        $sqlCheck = "SELECT idCliente FROM cliente WHERE telefono = :telefono";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->execute([':telefono' => $telefono]);
        
        if ($stmtCheck->fetch()) {
            http_response_code(409);
            echo json_encode([
                "success" => false,
                "error" => "Ya existe un cliente con ese número de teléfono."
            ]);
            exit;
        }
        
        // Insertar nuevo cliente (idTipoCliente = 2 para Mayorista)
        $sqlInsert = "INSERT INTO cliente (idTipoCliente, nombre, apellidoPaterno, apellidoMaterno, telefono) 
                      VALUES (2, :nombre, :apellidoPaterno, :apellidoMaterno, :telefono)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->execute([
            ':nombre' => $nombre,
            ':apellidoPaterno' => $apellidoPaterno,
            ':apellidoMaterno' => $apellidoMaterno,
            ':telefono' => $telefono
        ]);
        
        $idCliente = $conn->lastInsertId();
        $conn->commit();
        
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "mensaje" => "Cliente creado exitosamente.",
            "cliente" => [
                "idCliente" => $idCliente,
                "nombre" => $nombre,
                "apellidoPaterno" => $apellidoPaterno,
                "apellidoMaterno" => $apellidoMaterno,
                "nombreCompleto" => trim($nombre . ' ' . $apellidoPaterno . ' ' . $apellidoMaterno),
                "telefono" => $telefono,
                "tipoCliente" => "Mayorista"
            ]
        ]);
        
    }
    // ==================== OBTENER CLIENTE PÚBLICO ====================
    elseif ($accion === 'getPublico') {
        // El cliente público siempre tiene idCliente = 1
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "cliente" => [
                "idCliente" => 1,
                "nombre" => null,
                "nombreCompleto" => "Público General",
                "tipoCliente" => "Publico",
                "idTipoCliente" => 1
            ]
        ]);
    }
    else {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Acción no válida. Use: buscar, crear, o getPublico"
        ]);
    }
    
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error de base de datos: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    if (isset($conn) && isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error del servidor: " . $e->getMessage()
    ]);
}
?>