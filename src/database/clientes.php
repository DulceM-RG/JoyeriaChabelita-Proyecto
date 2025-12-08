<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'connection.php';

/**
 * JOYERÍA CHABELITA - API DE CLIENTES MAYORISTAS
 * 
 * Acciones disponibles:
 * - obtenerTodos: Obtiene todos los clientes mayoristas
 * - buscar: Busca clientes por nombre o teléfono
 * - crear: Crea un nuevo cliente mayorista
 * - actualizar: Actualiza datos de un cliente
 * - eliminar: Elimina un cliente
 * - getPublico: Obtiene el cliente público (General)
 */

try {
    // ==================== VERIFICAR AUTENTICACIÓN ====================
    if (!isset($_SESSION['usuario'])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "error" => "No hay sesión activa."
        ]);
        exit;
    }
    
    // ==================== OBTENER ENTRADA DE DATOS ====================
    $entrada = json_decode(file_get_contents('php://input'), true);
    $accion = $entrada['accion'] ?? '';
    
    // Conectar a base de datos
    $conn = ConexionDB::setConnection();
    
<<<<<<< HEAD
    // ==================== OBTENER TODOS LOS CLIENTES ====================
    if ($accion === 'obtenerTodos') {
        error_log("=== OBTENER TODOS LOS CLIENTES ===");
        
        $sqlObtener = "SELECT 
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
                      ORDER BY c.nombre ASC";
        
        try {
            $stmtObtener = $conn->prepare($sqlObtener);
            $stmtObtener->execute();
            
            $clientes = $stmtObtener->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("✅ Clientes obtenidos: " . count($clientes));
            
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
        exit;
    }
    
    // ==================== BUSCAR CLIENTE ====================
    elseif ($accion === 'buscar') {
        $busqueda = $entrada['busqueda'] ?? '';
        
        error_log("=== BÚSQUEDA DE CLIENTES ===");
        error_log("Término recibido: " . $busqueda);
        
        if (empty($busqueda)) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "Debe proporcionar un término de búsqueda."
            ]);
            exit;
        }
        
        // Preparar término de búsqueda con wildcards
        $terminoBusqueda = '%' . $busqueda . '%';
        
        error_log("Término con wildcards: " . $terminoBusqueda);
        
        // SQL CORREGIDA - Usar parámetros diferentes para cada condición
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
=======
    
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
>>>>>>> edbb1291e403ad1605072d72dac4f44e8ba7a5c1
            
            // CRÍTICO: Vincular CADA parámetro por separado
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
                
                http_response_code(200);
                echo json_encode([
                    "success" => false,
                    "error" => "No se encontraron clientes con ese criterio",
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
<<<<<<< HEAD
                "error" => "Error en la base de datos: " . $e->getMessage()
=======
                "error" => "No se encontraron clientes con ese criterio.",
                "clientes" => [],
                "busqueda" => $busqueda
>>>>>>> edbb1291e403ad1605072d72dac4f44e8ba7a5c1
            ]);
        }
<<<<<<< HEAD
        exit;
    }
    
=======
        
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

>>>>>>> edbb1291e403ad1605072d72dac4f44e8ba7a5c1
    // ==================== CREAR CLIENTE ====================
    elseif ($accion === 'crear') {
        error_log("=== CREAR NUEVO CLIENTE ===");
        
        $nombre = trim($entrada['nombre'] ?? '');
        $apellidoPaterno = trim($entrada['apellidoPaterno'] ?? '');
        $apellidoMaterno = trim($entrada['apellidoMaterno'] ?? '');
        $telefono = trim($entrada['telefono'] ?? '');
        
        error_log("Datos recibidos - Nombre: $nombre, AP: $apellidoPaterno, AM: $apellidoMaterno, Tel: $telefono");
        
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
        
        try {
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
            
            error_log("✅ Cliente creado exitosamente - ID: $idCliente");
            
            http_response_code(201);
            echo json_encode([
                "success" => true,
                "message" => "Cliente creado exitosamente.",
                "cliente" => [
                    "idCliente" => $idCliente,
                    "nombre" => $nombre,
                    "apellidoPaterno" => $apellidoPaterno,
                    "apellidoMaterno" => $apellidoMaterno,
                    "nombreCompleto" => trim($nombre . ' ' . $apellidoPaterno . ' ' . $apellidoMaterno),
                    "telefono" => $telefono,
                    "tipoCliente" => "Mayorista",
                    "idTipoCliente" => 2
                ]
            ]);
            
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("❌ Error al crear cliente: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "error" => "Error al crear el cliente: " . $e->getMessage()
            ]);
        }
        exit;
    }
    
    // ==================== ACTUALIZAR CLIENTE ====================
    elseif ($accion === 'actualizar') {
        error_log("=== ACTUALIZAR CLIENTE ===");
        
        $idCliente = intval($entrada['idCliente'] ?? 0);
        $nombre = trim($entrada['nombre'] ?? '');
        $apellidoPaterno = trim($entrada['apellidoPaterno'] ?? '');
        $apellidoMaterno = trim($entrada['apellidoMaterno'] ?? '');
        $telefono = trim($entrada['telefono'] ?? '');
        
        error_log("Actualizando cliente ID: $idCliente");
        
        // Validaciones
        if ($idCliente <= 0) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "ID de cliente inválido."
            ]);
            exit;
        }
        
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
        
        try {
            $conn->beginTransaction();
            
            // Verificar si el cliente existe
            $sqlCheckCliente = "SELECT idCliente FROM cliente WHERE idCliente = :idCliente";
            $stmtCheckCliente = $conn->prepare($sqlCheckCliente);
            $stmtCheckCliente->execute([':idCliente' => $idCliente]);
            
            if (!$stmtCheckCliente->fetch()) {
                http_response_code(404);
                echo json_encode([
                    "success" => false,
                    "error" => "El cliente no existe."
                ]);
                exit;
            }
            
            // Verificar si el nuevo teléfono ya existe (pero no del mismo cliente)
            $sqlCheckTel = "SELECT idCliente FROM cliente WHERE telefono = :telefono AND idCliente != :idCliente";
            $stmtCheckTel = $conn->prepare($sqlCheckTel);
            $stmtCheckTel->execute([
                ':telefono' => $telefono,
                ':idCliente' => $idCliente
            ]);
            
            if ($stmtCheckTel->fetch()) {
                http_response_code(409);
                echo json_encode([
                    "success" => false,
                    "error" => "Ya existe otro cliente con ese número de teléfono."
                ]);
                exit;
            }
            
            // Actualizar cliente
            $sqlUpdate = "UPDATE cliente 
                         SET nombre = :nombre,
                             apellidoPaterno = :apellidoPaterno,
                             apellidoMaterno = :apellidoMaterno,
                             telefono = :telefono
                         WHERE idCliente = :idCliente";
            
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->execute([
                ':nombre' => $nombre,
                ':apellidoPaterno' => $apellidoPaterno,
                ':apellidoMaterno' => $apellidoMaterno,
                ':telefono' => $telefono,
                ':idCliente' => $idCliente
            ]);
            
            $conn->commit();
            
            error_log("✅ Cliente actualizado exitosamente - ID: $idCliente");
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Cliente actualizado exitosamente.",
                "cliente" => [
                    "idCliente" => $idCliente,
                    "nombre" => $nombre,
                    "apellidoPaterno" => $apellidoPaterno,
                    "apellidoMaterno" => $apellidoMaterno,
                    "nombreCompleto" => trim($nombre . ' ' . $apellidoPaterno . ' ' . $apellidoMaterno),
                    "telefono" => $telefono,
                    "tipoCliente" => "Mayorista",
                    "idTipoCliente" => 2
                ]
            ]);
            
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("❌ Error al actualizar cliente: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "error" => "Error al actualizar el cliente: " . $e->getMessage()
            ]);
        }
        exit;
    }
    
    // ==================== ELIMINAR CLIENTE ====================
    elseif ($accion === 'eliminar') {
        error_log("=== ELIMINAR CLIENTE ===");
        
        $idCliente = intval($entrada['idCliente'] ?? 0);
        
        error_log("Eliminando cliente ID: $idCliente");
        
        // Validar ID
        if ($idCliente <= 0) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "error" => "ID de cliente inválido."
            ]);
            exit;
        }
        
        try {
            $conn->beginTransaction();
            
            // Verificar si el cliente existe
            $sqlCheck = "SELECT idCliente, nombre, apellidoPaterno, apellidoMaterno 
                        FROM cliente 
                        WHERE idCliente = :idCliente";
            $stmtCheck = $conn->prepare($sqlCheck);
            $stmtCheck->execute([':idCliente' => $idCliente]);
            
            $cliente = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if (!$cliente) {
                http_response_code(404);
                echo json_encode([
                    "success" => false,
                    "error" => "El cliente no existe."
                ]);
                exit;
            }
            
            // Eliminar cliente (cascada se ejecuta automáticamente)
            $sqlDelete = "DELETE FROM cliente WHERE idCliente = :idCliente";
            $stmtDelete = $conn->prepare($sqlDelete);
            $stmtDelete->execute([':idCliente' => $idCliente]);
            
            $conn->commit();
            
            $nombreCompleto = trim($cliente['nombre'] . ' ' . $cliente['apellidoPaterno'] . ' ' . $cliente['apellidoMaterno']);
            error_log("✅ Cliente eliminado exitosamente - Nombre: $nombreCompleto");
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Cliente eliminado exitosamente.",
                "cliente_eliminado" => $nombreCompleto
            ]);
            
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("❌ Error al eliminar cliente: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "error" => "Error al eliminar el cliente: " . $e->getMessage()
            ]);
        }
        exit;
    }
    
    // ==================== OBTENER CLIENTE PÚBLICO ====================
    elseif ($accion === 'getPublico') {
        error_log("=== OBTENER CLIENTE PÚBLICO ===");
        
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
        exit;
    }
    
    // ==================== ACCIÓN NO VÁLIDA ====================
    else {
        error_log("⚠️ Acción no válida: " . $accion);
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Acción no válida. Use: obtenerTodos, buscar, crear, actualizar, eliminar, o getPublico"
        ]);
    }
    
} catch (PDOException $e) {
    error_log("❌ Error de base de datos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error de base de datos: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("❌ Error del servidor: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error del servidor: " . $e->getMessage()
    ]);
}
?>