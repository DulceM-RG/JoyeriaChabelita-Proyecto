<?php
// Incluir la clase de conexión (ajusta la ruta si es necesario)
require_once 'ConexionDB.php'; // Archivo donde está la clase ConexionDB

// Función para verificar login y extraer datos de la tabla 'credenciales'
function verificarLogin($credencialesInput, $password) {
    try {
        $pdo = ConexionDB::setConnection();
        
        // Preparar consulta para buscar en la tabla 'credenciales' por el campo 'credenciales'
        $stmt = $pdo->prepare("SELECT id, credenciales, password, rol, intentos_fallidos FROM credenciales WHERE credenciales = :credenciales");
        $stmt->bindParam(':credenciales', $credencialesInput, PDO::PARAM_STR);
        $stmt->execute();
        
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            return ['success' => false, 'error' => 'Credenciales no encontradas.'];
        }
        
        // Verificar si la cuenta está bloqueada (3 o más intentos fallidos)
        if ($usuario['intentos_fallidos'] >= 3) {
            return ['success' => false, 'error' => 'Cuenta bloqueada después de 3 intentos fallidos. Comunícate con el gerente para revisar tu problema.'];
        }
        
        if (password_verify($password, $usuario['password'])) {
            // Contraseña correcta: resetear intentos fallidos y devolver datos
            $stmtReset = $pdo->prepare("UPDATE credenciales SET intentos_fallidos = 0 WHERE id = :id");
            $stmtReset->bindParam(':id', $usuario['id'], PDO::PARAM_INT);
            $stmtReset->execute();
            
            return [
                'id' => $usuario['id'],
                'credenciales' => $usuario['credenciales'],
                'rol' => $usuario['rol'],
                'success' => true
            ];
        } else {
            // Contraseña incorrecta: incrementar intentos fallidos
            $nuevosIntentos = $usuario['intentos_fallidos'] + 1;
            $stmtUpdate = $pdo->prepare("UPDATE credenciales SET intentos_fallidos = :intentos WHERE id = :id");
            $stmtUpdate->bindParam(':intentos', $nuevosIntentos, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':id', $usuario['id'], PDO::PARAM_INT);
            $stmtUpdate->execute();
            
            $intentosRestantes = 3 - $nuevosIntentos;
            if ($intentosRestantes > 0) {
                return ['success' => false, 'error' => "Contraseña incorrecta. Intentos restantes: $intentosRestantes"];
            } else {
                return ['success' => false, 'error' => 'Cuenta bloqueada después de 3 intentos fallidos. Comunícate con el gerente para revisar tu problema.'];
            }
        }
    } catch (PDOException $e) {
        error_log("Error en login: " . $e->getMessage());
        return ['success' => false, 'error' => 'Error interno del servidor'];
    }
}

// Procesar el formulario POST desde el HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $credenciales = trim($_POST['credenciales'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validación básica del formato de credenciales (9 caracteres, empezando con G/V/A/C)
    if (strlen($credenciales) !== 9 || !preg_match('/^[GVAC][A-Za-z0-9]{8}$/', $credenciales)) {
        echo json_encode(['success' => false, 'error' => 'Credenciales inválidas. Deben tener exactamente 9 caracteres y empezar con G, V, A o C.']);
        exit;
    }
    
    if (empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Por favor, ingresa tu contraseña.']);
        exit;
    }
    
    // Verificar login contra la base de datos
    $resultado = verificarLogin($credenciales, $password);
    
    if ($resultado['success']) {
        // Login exitoso: iniciar sesión
        session_start();
        $_SESSION['usuario'] = $resultado;
        
        // Redirecciones basadas en el rol
        // Solo el rol 'G' (Gerente) puede acceder a 'credenciales.html' (como administrador)
        // Los otros roles van a sus páginas específicas
        $rol = $resultado['rol'];
        $redirecciones = [
            'G' => '/JoyeriaChabelita-Proyecto/credenciales.html', // Gerente: acceso a credenciales
            'V' => '/JoyeriaChabelita-Proyecto/venta.html',       // Venta
            'A' => '/JoyeriaChabelita-Proyecto/almacen.html',     // Almacén
            'C' => '/JoyeriaChabelita-Proyecto/contador.html'     // Contador
        ];
        
        if (isset($redirecciones[$rol])) {
            echo json_encode(['success' => true, 'redirect' => $redirecciones[$rol]]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Rol no reconocido.']);
        }
    } else {
        echo json_encode($resultado);
    }
} else {
    // Si no es una solicitud POST, devolver error
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>