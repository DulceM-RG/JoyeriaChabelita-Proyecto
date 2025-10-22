<?php
require_once 'connection.php';

echo "<h2>Prueba de Conexión - Sistema de Login</h2>";

try {
    // 1. Probar conexión
    $conn = ConexionDB::setConnection();
    echo "✅ Conexión a base de datos exitosa<br><br>";
    
    // 2. Verificar tablas
    echo "<h3>Verificando tablas:</h3>";
    $tables = ['credenciales', 'empleado', 'puestoempleado'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✅ Tabla '$table' - Registros: " . $result['count'] . "<br>";
        } catch (PDOException $e) {
            echo "❌ Error en tabla '$table': " . $e->getMessage() . "<br>";
        }
    }
    
    // 3. Probar la consulta del login (sin contraseña)
    echo "<br><h3>Probando consulta de login:</h3>";
    
    // Primero obtener un idControl de ejemplo
    $stmtId = $conn->query("SELECT idControl FROM credenciales LIMIT 1");
    $ejemploId = $stmtId->fetch(PDO::FETCH_ASSOC);
    
    if ($ejemploId) {
        $idControl = $ejemploId['idControl'];
        echo "Probando con ID: <strong>$idControl</strong><br><br>";
        
        $sql = "SELECT 
                    c.idControl,
                    c.idEmpleado,
                    c.intentosFallidos,
                    c.activo,
                    e.nombre,
                    e.apellidoPaterno,
                    e.apellidoMaterno,
                    e.idPuesto,
                    p.puesto,
                    p.sueldo
                FROM credenciales c
                INNER JOIN empleado e ON c.idEmpleado = e.idEmpleado
                INNER JOIN puestoempleado p ON e.idPuesto = p.idPuesto
                WHERE c.idControl = :idControl";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([':idControl' => $idControl]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            echo "✅ Consulta exitosa. Usuario encontrado:<br>";
            echo "<pre>";
            print_r($usuario);
            echo "</pre>";
        } else {
            echo "❌ No se encontró el usuario";
        }
    } else {
        echo "⚠️ No hay registros en la tabla credenciales";
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage();
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
