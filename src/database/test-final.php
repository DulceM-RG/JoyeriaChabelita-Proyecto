<?php
// test-final.php - Diagnóstico completo
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

echo "<h2>🔍 Diagnóstico de Connection.php</h2>";

// Test 1: Verificar que el archivo existe
$archivoConnection = __DIR__ . '/connection.php';
echo "<h3>1. Verificar existencia del archivo</h3>";
echo "Ruta: $archivoConnection<br>";
echo "Existe: " . (file_exists($archivoConnection) ? '✅ SÍ' : '❌ NO') . "<br>";

if (!file_exists($archivoConnection)) {
    die("❌ ERROR: El archivo no existe");
}

// Test 2: Leer contenido del archivo
echo "<h3>2. Contenido del archivo (primeros 200 caracteres)</h3>";
$contenido = file_get_contents($archivoConnection);
echo "<pre>" . htmlspecialchars(substr($contenido, 0, 200)) . "</pre>";

// Test 3: Verificar BOM
echo "<h3>3. Verificar BOM (Byte Order Mark)</h3>";
$bom = substr($contenido, 0, 3);
if ($bom === "\xEF\xBB\xBF") {
    echo "❌ ERROR: El archivo tiene BOM UTF-8<br>";
    echo "⚠️ Esto causa problemas. Debes guardarlo como UTF-8 SIN BOM<br>";
} else {
    echo "✅ OK: Sin BOM detectado<br>";
}

// Test 4: Verificar espacios antes de <?php
echo "<h3>4. Verificar espacios antes de &lt;?php</h3>";
if (preg_match('/^[\s\r\n]+<\?php/', $contenido)) {
    echo "❌ ERROR: Hay espacios en blanco antes de &lt;?php<br>";
} else {
    echo "✅ OK: No hay espacios antes de &lt;?php<br>";
}

// Test 5: Intentar incluir el archivo
echo "<h3>5. Intentar incluir connection.php</h3>";
try {
    require_once $archivoConnection;
    echo "✅ Archivo incluido sin errores<br>";
} catch (Exception $e) {
    echo "❌ ERROR al incluir: " . $e->getMessage() . "<br>";
    die();
}

// Test 6: Verificar variable $conexion
echo "<h3>6. Verificar variable \$conexion</h3>";
if (isset($conexion)) {
    echo "✅ Variable \$conexion está definida<br>";
    echo "Tipo: " . get_class($conexion) . "<br>";
    
    // Test 7: Probar conexión
    echo "<h3>7. Probar conexión a la base de datos</h3>";
    if ($conexion->ping()) {
        echo "✅ Conexión activa y funcionando<br>";
        echo "Base de datos: " . DB_NAME . "<br>";
        echo "Servidor: " . DB_HOST . "<br>";
    } else {
        echo "❌ ERROR: Conexión no responde<br>";
    }
} else {
    echo "❌ ERROR CRÍTICO: La variable \$conexion NO está definida<br>";
    echo "⚠️ Esto significa que connection.php no está creando la variable correctamente<br>";
}

echo "<hr>";
echo "<h3>✅ DIAGNÓSTICO COMPLETO</h3>";
?>