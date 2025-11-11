<?php
/**
 * Archivo de diagnóstico para clientesMayoristas.php
 * Coloca este archivo en: src/php/test_mayoristas.php
 * Accede desde: http://localhost/tu-proyecto/src/php/test_mayoristas.php
 */

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$diagnostico = [
    'tests' => [],
    'archivos' => []
];

// ========== TEST 1: Verificar que connection.php existe ==========
$pathConnection = __DIR__ . '/connection.php';
if (file_exists($pathConnection)) {
    $diagnostico['tests'][] = [
        'test' => 'Archivo connection.php',
        'estado' => 'OK',
        'path' => $pathConnection
    ];
    require_once $pathConnection;
} else {
    $diagnostico['tests'][] = [
        'test' => 'Archivo connection.php',
        'estado' => 'ERROR',
        'mensaje' => 'No se encuentra el archivo',
        'path_buscado' => $pathConnection
    ];
}

// ========== TEST 2: Verificar que clientesMayoristas.php existe ==========
$pathMayoristas = __DIR__ . '/clientesMayoristas.php';
if (file_exists($pathMayoristas)) {
    $diagnostico['tests'][] = [
        'test' => 'Archivo clientesMayoristas.php',
        'estado' => 'OK',
        'path' => $pathMayoristas,
        'permisos' => substr(sprintf('%o', fileperms($pathMayoristas)), -4)
    ];
} else {
    $diagnostico['tests'][] = [
        'test' => 'Archivo clientesMayoristas.php',
        'estado' => 'ERROR',
        'mensaje' => 'No se encuentra el archivo',
        'path_buscado' => $pathMayoristas
    ];
}

// ========== TEST 3: Probar conexión a la BD ==========
try {
    $conn = ConexionDB::setConnection();
    $diagnostico['tests'][] = [
        'test' => 'Conexión a BD',
        'estado' => 'OK'
    ];
} catch (Exception $e) {
    $diagnostico['tests'][] = [
        'test' => 'Conexión a BD',
        'estado' => 'ERROR',
        'mensaje' => $e->getMessage()
    ];
    echo json_encode($diagnostico, JSON_PRETTY_PRINT);
    exit;
}

// ========== TEST 4: Verificar tabla cliente (SINGULAR) ==========
try {
    $sql = "SELECT COUNT(*) as total FROM cliente";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $diagnostico['tests'][] = [
        'test' => 'Tabla cliente',
        'estado' => 'OK',
        'total_registros' => $result['total']
    ];
} catch (PDOException $e) {
    $diagnostico['tests'][] = [
        'test' => 'Tabla cliente',
        'estado' => 'ERROR',
        'mensaje' => $e->getMessage()
    ];
}

// ========== TEST 5: Verificar clientes con idTipoCliente = 2 (SINGULAR) ==========
try {
    $sql = "SELECT COUNT(*) as total FROM cliente WHERE idTipoCliente = 2";
    $stmt = $conn->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['total'] > 0) {
        $diagnostico['tests'][] = [
            'test' => 'Clientes mayoristas (idTipoCliente=2)',
            'estado' => 'OK',
            'total' => $result['total']
        ];
    } else {
        $diagnostico['tests'][] = [
            'test' => 'Clientes mayoristas (idTipoCliente=2)',
            'estado' => 'ADVERTENCIA',
            'mensaje' => 'No hay clientes mayoristas en la BD',
            'total' => 0
        ];
    }
} catch (PDOException $e) {
    $diagnostico['tests'][] = [
        'test' => 'Clientes mayoristas',
        'estado' => 'ERROR',
        'mensaje' => $e->getMessage()
    ];
}

// ========== TEST 6: Simular petición GET (obtenerClientes) - SINGULAR ==========
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
            ORDER BY idCliente ASC 
            LIMIT 3";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $diagnostico['tests'][] = [
        'test' => 'Consulta SQL (obtenerClientes)',
        'estado' => 'OK',
        'registros_obtenidos' => count($clientes),
        'ejemplo' => $clientes
    ];
} catch (PDOException $e) {
    $diagnostico['tests'][] = [
        'test' => 'Consulta SQL (obtenerClientes)',
        'estado' => 'ERROR',
        'mensaje' => $e->getMessage()
    ];
}

// ========== TEST 7: Probar el endpoint real ==========
$diagnostico['tests'][] = [
    'test' => 'Prueba del endpoint real',
    'estado' => 'INFO',
    'urls_para_probar' => [
        'GET obtenerClientes' => 'clientesMayoristas.php?action=obtenerClientes',
        'Ruta completa' => $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/clientesMayoristas.php?action=obtenerClientes'
    ]
];

// ========== TEST 8: Verificar errores comunes ==========
$erroresComunes = [];

// Verificar sesión
if (session_status() === PHP_SESSION_NONE) {
    $erroresComunes[] = 'La sesión no está iniciada (puede ser necesario según tu código)';
}

// Verificar error_reporting
if (error_reporting() === 0) {
    $erroresComunes[] = 'error_reporting está desactivado, actívalo para ver errores';
}

$diagnostico['tests'][] = [
    'test' => 'Configuración PHP',
    'estado' => empty($erroresComunes) ? 'OK' : 'ADVERTENCIA',
    'errores_comunes' => $erroresComunes,
    'error_reporting' => error_reporting(),
    'display_errors' => ini_get('display_errors')
];

// ========== RESUMEN ==========
$errores = array_filter($diagnostico['tests'], function($test) {
    return $test['estado'] === 'ERROR';
});

$diagnostico['resumen'] = [
    'total_tests' => count($diagnostico['tests']),
    'errores' => count($errores),
    'estado' => count($errores) > 0 ? '❌ HAY ERRORES' : '✅ TODO BIEN',
    'siguiente_paso' => count($errores) > 0 ? 
        'Revisa los errores arriba' : 
        'Prueba: clientesMayoristas.php?action=obtenerClientes'
];

// ========== INFORMACIÓN ADICIONAL ==========
$diagnostico['info'] = [
    'directorio_actual' => __DIR__,
    'servidor' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido',
    'php_version' => phpversion(),
    'metodo_request' => $_SERVER['REQUEST_METHOD'] ?? 'CLI'
];

echo json_encode($diagnostico, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>