<?php
session_start();
header('Content-Type: application/json');
require_once 'connection.php';

try {
    // Recibir datos del login
    $entrada = json_decode(file_get_contents('php://input'), true);
    
    $idControl = $entrada['idControl'] ?? '';
    $contrasena = $entrada['contrasena'] ?? '';
    
    // Validar que los campos no estén vacíos
    if (empty($idControl) || empty($contrasena)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "errorLogin" => "Por favor, complete todos los campos."
        ]);
        exit;
    }
    
    $conn = ConexionDB::setConnection();
    
    // Consulta para obtener datos del usuario con su puesto
    $sql = "SELECT 
                c.idControl,
                c.idEmpleado,
                c.contrasena,
                c.intentosFallidos,
                c.activo,
                e.nombre,
                e.apellidoPaterno,
                e.apellidoMaterno,
                e.telefono,
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
    
    // Verificar si el usuario existe
    if (!$usuario) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "errorLogin" => "ID de Control no encontrado."
        ]);
        exit;
    }
    
    // Verificar si la cuenta está activa
    if ($usuario['activo'] !== 'Activo') {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "errorLogin" => "Esta cuenta ha sido desactivada. Contacte al administrador."
        ]);
        exit;
    }
    
    // Verificar intentos fallidos (bloqueo de cuenta después de 5 intentos)
    if ($usuario['intentosFallidos'] >= 5) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "errorLogin" => "Cuenta bloqueada por múltiples intentos fallidos. Contacte al administrador."
        ]);
        exit;
    }
    
    // Verificar contraseña
    if (password_verify($contrasena, $usuario['contrasena'])) {
        // ✅ CONTRASEÑA CORRECTA
        
        // Resetear intentos fallidos
        $sqlReset = "UPDATE credenciales SET intentosFallidos = 0 WHERE idControl = :idControl";
        $stmtReset = $conn->prepare($sqlReset);
        $stmtReset->execute([':idControl' => $idControl]);
        
        // Crear sesión
        $_SESSION['usuario'] = [
            'idControl' => $usuario['idControl'],
            'idEmpleado' => $usuario['idEmpleado'],
            'nombre' => $usuario['nombre'],
            'apellidoPaterno' => $usuario['apellidoPaterno'],
            'apellidoMaterno' => $usuario['apellidoMaterno'],
            'nombreCompleto' => $usuario['nombre'] . ' ' . $usuario['apellidoPaterno'] . ' ' . $usuario['apellidoMaterno'],
            'puesto' => strtolower($usuario['puesto']),
            'idPuesto' => $usuario['idPuesto'],
            'sueldo' => $usuario['sueldo']
        ];
        
        // Respuesta exitosa
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "mensaje" => "Inicio de sesión exitoso",
            "usuario" => [
                'idControl' => $usuario['idControl'],
                'idEmpleado' => $usuario['idEmpleado'],
                'nombre' => $usuario['nombre'],
                'apellidoPaterno' => $usuario['apellidoPaterno'],
                'apellidoMaterno' => $usuario['apellidoMaterno'],
                'nombreCompleto' => $usuario['nombre'] . ' ' . $usuario['apellidoPaterno'] . ' ' . $usuario['apellidoMaterno'],
                'puesto' => strtolower($usuario['puesto']),
                'idPuesto' => $usuario['idPuesto']
            ]
        ]);
        
    } else {
        // ❌ CONTRASEÑA INCORRECTA
        
        // Incrementar intentos fallidos
        $intentos = $usuario['intentosFallidos'] + 1;
        $sqlIntentos = "UPDATE credenciales SET intentosFallidos = :intentos WHERE idControl = :idControl";
        $stmtIntentos = $conn->prepare($sqlIntentos);
        $stmtIntentos->execute([
            ':intentos' => $intentos,
            ':idControl' => $idControl
        ]);
        
        $intentosRestantes = 5 - $intentos;
        
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "errorLogin" => "Contraseña incorrecta. Intentos restantes: " . max(0, $intentosRestantes)
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "errorLogin" => "Error del servidor: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "errorLogin" => "Error inesperado: " . $e->getMessage()
    ]);
}
?>
