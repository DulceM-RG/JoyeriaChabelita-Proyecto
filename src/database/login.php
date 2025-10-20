<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/connection.php';

$response = ['success' => false, 'error' => 'Credenciales inválidas.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $credenciales = trim($_POST['credenciales'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($credenciales) || empty($password)) {
        $response['error'] = 'Por favor, introduce tus credenciales y contraseña.';
    } else {
        try {
            $pdo = ConexionDB::setConnection();
            $stmt = $pdo->prepare("SELECT credenciales, password, rol, activo FROM usuarios WHERE credenciales = :credenciales");
            $stmt->bindParam(':credenciales', $credenciales, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user) {
                if ($user['activo'] === 'Baja') {
                    $response['error'] = 'Tu cuenta ha sido dada de baja o está inactiva.';
                } elseif (password_verify($password, $user['password'])) {
                    $_SESSION['loggedin'] = true;
                    $_SESSION['credenciales'] = $user['credenciales'];
                    $_SESSION['rol'] = $user['rol'];

                    switch ($user['rol']) {
                        case 'administrador':
                            $response['redirect'] = 'dashboard_admin.html';
                            break;
                        case 'vendedor':
                            $response['redirect'] = 'dashboard_vendedor.html';
                            break;
                        default:
                            $response['redirect'] = 'home.html';
                            break;
                    }

                    $response['success'] = true;
                } else {
                    $response['error'] = 'Credenciales o contraseña incorrecta.';
                }
            } else {
                $response['error'] = 'Credenciales o contraseña incorrecta.';
            }
        } catch (PDOException $e) {
            $response['error'] = 'Error interno del servidor. Inténtalo más tarde.';
        }
    }
} else {
    $response['error'] = 'Método de solicitud no permitido.';
}

echo json_encode($response);
exit;
?>
