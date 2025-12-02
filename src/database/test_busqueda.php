<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'connection.php';

echo "====================================\n";
echo "TEST DE BÚSQUEDA DE CLIENTES\n";
echo "====================================\n\n";

try {
    $conn = ConexionDB::setConnection();
    echo "✅ Conexión establecida\n\n";
    
    // ============================================
    // TEST 1: Ver todos los clientes mayoristas
    // ============================================
    echo "--- TEST 1: TODOS LOS CLIENTES MAYORISTAS ---\n";
    $sql1 = "SELECT 
                c.idCliente,
                c.nombre,
                c.apellidoPaterno,
                c.apellidoMaterno,
                c.telefono,
                c.idTipoCliente,
                CONCAT_WS(' ', c.nombre, c.apellidoPaterno, c.apellidoMaterno) as nombreCompleto
             FROM cliente c
             WHERE c.idTipoCliente = 2";
    
    $stmt1 = $conn->prepare($sql1);
    $stmt1->execute();
    $todos = $stmt1->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total encontrados: " . count($todos) . "\n";
    foreach ($todos as $cliente) {
        echo "  - ID: {$cliente['idCliente']} | {$cliente['nombreCompleto']} | Tel: {$cliente['telefono']}\n";
    }
    echo "\n";
    
    // ============================================
    // TEST 2: Buscar por teléfono exacto
    // ============================================
    echo "--- TEST 2: BUSCAR POR TELÉFONO (5551234562) ---\n";
    $telefono = "5551234562";
    $sql2 = "SELECT * FROM cliente WHERE telefono = :telefono AND idTipoCliente = 2";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->execute([':telefono' => $telefono]);
    $porTelefono = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    if ($porTelefono) {
        echo "✅ Encontrado: " . json_encode($porTelefono[0], JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "❌ NO encontrado\n";
    }
    echo "\n";
    
    // ============================================
    // TEST 3: Buscar por teléfono con LIKE
    // ============================================
    echo "--- TEST 3: BUSCAR CON LIKE '%5551234562%' ---\n";
    $sql3 = "SELECT * FROM cliente WHERE telefono LIKE :telefono AND idTipoCliente = 2";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->execute([':telefono' => '%5551234562%']);
    $conLike = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Encontrados con LIKE: " . count($conLike) . "\n";
    if ($conLike) {
        echo "✅ Primer resultado: " . json_encode($conLike[0], JSON_UNESCAPED_UNICODE) . "\n";
    }
    echo "\n";
    
    // ============================================
    // TEST 4: Buscar por nombre "Josefa"
    // ============================================
    echo "--- TEST 4: BUSCAR POR NOMBRE 'Josefa' ---\n";
    $sql4 = "SELECT * FROM cliente WHERE nombre LIKE :nombre AND idTipoCliente = 2";
    $stmt4 = $conn->prepare($sql4);
    $stmt4->execute([':nombre' => '%Josefa%']);
    $porNombre = $stmt4->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Encontrados: " . count($porNombre) . "\n";
    if ($porNombre) {
        echo "✅ Resultados:\n";
        foreach ($porNombre as $c) {
            echo "  - {$c['nombre']} {$c['apellidoPaterno']} | Tel: {$c['telefono']}\n";
        }
    }
    echo "\n";
    
    // ============================================
    // TEST 5: Buscar como lo hace tu código actual
    // ============================================
    echo "--- TEST 5: BÚSQUEDA COMPLETA (como tu código) ---\n";
    $busqueda = "Josefa";
    $terminoBusqueda = '%' . $busqueda . '%';
    
    $sqlCompleto = "SELECT 
                        c.idCliente,
                        c.nombre,
                        c.apellidoPaterno,
                        c.apellidoMaterno,
                        c.telefono,
                        c.idTipoCliente,
                        CONCAT_WS(' ', c.nombre, c.apellidoPaterno, c.apellidoMaterno) as nombreCompleto
                    FROM cliente c
                    WHERE c.idTipoCliente = 2
                    AND (
                        c.telefono LIKE :busqueda1 
                        OR CONCAT_WS(' ', c.nombre, c.apellidoPaterno, c.apellidoMaterno) LIKE :busqueda2
                        OR c.nombre LIKE :busqueda3
                    )
                    LIMIT 20";
    
    $stmtCompleto = $conn->prepare($sqlCompleto);
    $stmtCompleto->bindValue(':busqueda1', $terminoBusqueda, PDO::PARAM_STR);
    $stmtCompleto->bindValue(':busqueda2', $terminoBusqueda, PDO::PARAM_STR);
    $stmtCompleto->bindValue(':busqueda3', $terminoBusqueda, PDO::PARAM_STR);
    $stmtCompleto->execute();
    $resultadoCompleto = $stmtCompleto->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Término buscado: '{$busqueda}'\n";
    echo "Con wildcards: '{$terminoBusqueda}'\n";
    echo "Resultados: " . count($resultadoCompleto) . "\n";
    
    if ($resultadoCompleto) {
        echo "✅ ÉXITO - Clientes encontrados:\n";
        echo json_encode($resultadoCompleto, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "❌ NO se encontraron resultados\n";
    }
    echo "\n";
    
    // ============================================
    // TEST 6: Verificar estructura de la tabla
    // ============================================
    echo "--- TEST 6: ESTRUCTURA DE LA TABLA ---\n";
    $sqlEstructura = "DESCRIBE cliente";
    $stmtEst = $conn->query($sqlEstructura);
    $estructura = $stmtEst->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columnas de la tabla 'cliente':\n";
    foreach ($estructura as $columna) {
        echo "  - {$columna['Field']} ({$columna['Type']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n====================================\n";
echo "FIN DEL TEST\n";
echo "====================================\n";
?>