<?php
session_start();
header('Content-Type: application/json');
require_once 'connection.php';

// 📝 Función para escribir logs (para debugging)
function escribirLog($mensaje) {
    $log = date('Y-m-d H:i:s') . " - " . $mensaje . "\n";
    file_put_contents('login_debug.log', $log, FILE_APPEND);
}

try {
    escribirLog("=== INICIO DE LOGIN ===");
    
    // Recibir y validar JSON de entrada
    $entrada = json_decode(file_get_contents('php://input'), true);
    if ($entrada === null) {
        escribirLog("ERROR: Datos de entrada inválidos");
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "errorLogin" => "Datos de entrada inválidos."
        ]);
        exit;
    }
    
    $idControl = $entrada['idControl'] ?? '';
    $contrasena = $entrada['contrasena'] ?? '';
    
    escribirLog("Intentando login con ID: " . $idControl);
    
    // Validar que los campos no estén vacíos
    if (empty($idControl) || empty($contrasena)) {
        escribirLog("ERROR: Campos vacíos");
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "errorLogin" => "Por favor, complete todos los campos."
        ]);
        exit;
    }
    
    $conn = ConexionDB::setConnection();
    escribirLog("Conexión a BD establecida");
    
    // 🔒 VERIFICAR SI EL DÍA ESTÁ BLOQUEADO (DÍA YA CERRADO)
    $fechaHoy = date('Y-m-d');
    escribirLog("Verificando bloqueo para fecha: " . $fechaHoy);
    
    try {
        // Primero verificar si la tabla existe
        $checkTable = $conn->query("SHOW TABLES LIKE 'bloqueos_sesion'");
        if ($checkTable->rowCount() > 0) {
            escribirLog("Tabla bloqueos_sesion existe, verificando...");
            
            $stmtBloqueo = $conn->prepare("
                SELECT COUNT(*) as bloqueado, fechaBloqueo, activo
                FROM bloqueos_sesion
                WHERE fechaBloqueo = :fecha AND activo = 1
            ");
            $stmtBloqueo->execute(['fecha' => $fechaHoy]);
            $bloqueado = $stmtBloqueo->fetch(PDO::FETCH_ASSOC);
            
            escribirLog("Resultado bloqueo: " . json_encode($bloqueado));
            
            if ($bloqueado && $bloqueado['bloqueado'] > 0) {
                escribirLog("⛔ DÍA BLOQUEADO - Login rechazado");
                http_response_code(403);
                echo json_encode([
                    "success" => false,
                    "errorLogin" => "⛔ TURNO CERRADO\n\nEl día de hoy ya fue cerrado. No se permiten nuevos ingresos hasta mañana.",
                    "diaBloqueado" => true,
                    "fechaBloqueada" => $fechaHoy
                ]);
                exit;
            } else {
                escribirLog("✅ Día NO bloqueado, continuando...");
            }
        } else {
            escribirLog("⚠️ Tabla bloqueos_sesion NO existe, continuando sin validación");
        }
    } catch (PDOException $e) {
        escribirLog("⚠️ Error al verificar bloqueo: " . $e->getMessage());
        // Si hay error, continuar sin bloqueo (no queremos romper el login)
    }
    
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
                p.puesto
            FROM credenciales c
            INNER JOIN empleado e ON c.idEmpleado = e.idEmpleado
            INNER JOIN puestoempleado p ON e.idPuesto = p.idPuesto
            WHERE c.idControl = :idControl";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([':idControl' => $idControl]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar si el usuario existe
    if (!$usuario) {
        escribirLog("ERROR: Usuario no encontrado");
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "errorLogin" => "ID de Control no encontrado."
        ]);
        exit;
    }
    
    escribirLog("Usuario encontrado: " . $usuario['nombre']);
    
    // Verificar si la cuenta está activa
    if ($usuario['activo'] !== 'Activo') {
        escribirLog("ERROR: Cuenta desactivada");
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "errorLogin" => "Esta cuenta ha sido desactivada. Contacte al administrador."
        ]);
        exit;
    }
    
    // Verificar intentos fallidos (bloqueo después de 3)
    if ($usuario['intentosFallidos'] >= 3) {
        escribirLog("ERROR: Cuenta bloqueada por intentos");
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "errorLogin" => "Cuenta bloqueada por múltiples intentos fallidos. Contacte al administrador."
        ]);
        exit;
    }
    
    // Verificar contraseña
    if (password_verify($contrasena, $usuario['contrasena'])) {
        escribirLog("✅ Contraseña correcta");
        
        // Resetear intentos fallidos
        $sqlReset = "UPDATE credenciales SET intentosFallidos = 0 WHERE idControl = :idControl";
        $stmtReset = $conn->prepare($sqlReset);
        $stmtReset->execute([':idControl' => $idControl]);
        
        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);
        
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
            'fechaLogin' => date('Y-m-d H:i:s')
        ];
        
        escribirLog("✅ LOGIN EXITOSO - Usuario: " . $usuario['nombre'] . " - Puesto: " . $usuario['puesto']);
        
        // Respuesta exitosa
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "mensaje" => "Inicio de sesión exitoso",
            "usuario" => [
                "idControl" => $usuario['idControl'],
                "idEmpleado" => $usuario['idEmpleado'],
                "nombre" => $usuario['nombre'],
                "apellidoPaterno" => $usuario['apellidoPaterno'],
                "apellidoMaterno" => $usuario['apellidoMaterno'],
                "nombreCompleto" => $usuario['nombre'] . ' ' . $usuario['apellidoPaterno'] . ' ' . $usuario['apellidoMaterno'],
                "puesto" => strtolower($usuario['puesto']),
                "idPuesto" => $usuario['idPuesto']
            ]
        ]);
        
    } else {
        escribirLog("ERROR: Contraseña incorrecta");
        
        // Incrementar intentos fallidos
        $intentos = $usuario['intentosFallidos'] + 1;
        $sqlIntentos = "UPDATE credenciales SET intentosFallidos = :intentos WHERE idControl = :idControl";
        $stmtIntentos = $conn->prepare($sqlIntentos);
        $stmtIntentos->execute([
            ':intentos' => $intentos,
            ':idControl' => $idControl
        ]);
        
        $intentosRestantes = 3 - $intentos;
        
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "errorLogin" => "Contraseña incorrecta. Intentos restantes: " . max(0, $intentosRestantes)
        ]);
    }
    
} catch (PDOException $e) {
    escribirLog("ERROR PDO: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "errorLogin" => "Error del servidor. Por favor, intente más tarde."
    ]);
} catch (Exception $e) {
    escribirLog("ERROR General: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "errorLogin" => "Error inesperado. Por favor, intente más tarde."
    ]);
}
?>