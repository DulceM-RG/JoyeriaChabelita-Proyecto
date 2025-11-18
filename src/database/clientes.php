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
    
    // 🔹 LOG inicial
    error_log("=== INICIO BÚSQUEDA ===");
    error_log("Término recibido: " . $busqueda);
    
    if (empty($busqueda)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Debe proporcionar un término de búsqueda."
        ]);
        exit;
    }
    
    // 🔹 Preparar término de búsqueda
    $terminoBusqueda = '%' . $busqueda . '%';
    
    error_log("Término con wildcards: " . $terminoBusqueda);
    
    // 🔹 SQL CORREGIDA - Usamos parámetros DIFERENTES para cada condición
    $sqlBuscar = "SELECT 
                    c.idCliente,
                    c.nombre,
                    c.apellidoPaterno,
                    c.apellidoMaterno,
                    c.telefono,
                    tc.tipo as tipoCliente,
                    c.idTipoCliente,
                    CONCAT_WS(' ', c.nombre, c.apellidoPaterno, c.apellidoMaterno) as nombreCompleto
                  FROM cliente c
                  INNER JOIN tipocliente tc ON c.idTipoCliente = tc.idTipoCliente
                  WHERE c.idTipoCliente = 2
                  AND (
                      c.telefono LIKE :busqueda1 
                      OR CONCAT_WS(' ', c.nombre, c.apellidoPaterno, c.apellidoMaterno) LIKE :busqueda2
                      OR c.nombre LIKE :busqueda3
                      OR c.apellidoPaterno LIKE :busqueda4
                      OR c.apellidoMaterno LIKE :busqueda5
                  )
                  ORDER BY c.nombre ASC
                  LIMIT 20";
    
    error_log("SQL preparado");
    
    try {
        $stmtBuscar = $conn->prepare($sqlBuscar);
        
        // 🔹 CRÍTICO: Vincular CADA parámetro por separado
        $stmtBuscar->bindValue(':busqueda1', $terminoBusqueda, PDO::PARAM_STR);
        $stmtBuscar->bindValue(':busqueda2', $terminoBusqueda, PDO::PARAM_STR);
        $stmtBuscar->bindValue(':busqueda3', $terminoBusqueda, PDO::PARAM_STR);
        $stmtBuscar->bindValue(':busqueda4', $terminoBusqueda, PDO::PARAM_STR);
        $stmtBuscar->bindValue(':busqueda5', $terminoBusqueda, PDO::PARAM_STR);
        
        error_log("Parámetros vinculados: " . $terminoBusqueda);
        
        $stmtBuscar->execute();
        
        error_log("Query ejecutada exitosamente");
        
        $clientes = $stmtBuscar->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Clientes encontrados: " . count($clientes));
        
        if (empty($clientes)) {
            error_log("⚠️ Sin resultados para: " . $busqueda);
            
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "error" => "No se encontraron clientes con ese criterio.",
                "clientes" => [],
                "busqueda" => $busqueda
            ]);
            exit;
        }
        
        error_log("✅ Éxito: " . count($clientes) . " clientes encontrados");
        
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "clientes" => $clientes,
            "cantidad" => count($clientes)
        ]);
        
    } catch (PDOException $e) {
        error_log("❌ Error SQL: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "error" => "Error en la base de datos: " . $e->getMessage()
        ]);
    }
    
    
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