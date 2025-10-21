<?php
header('Content-Type: application/json');
require_once 'connection.php';

try {
    $conn = ConexionDB::setConnection();

    $entrada = json_decode(file_get_contents("php://input"), true);
    $credenciales = trim($entrada['credenciales'] ?? '');
    $password = $entrada['password'] ?? '';

    if (empty($credenciales) || empty($password)) {
        http_response_code(400);
        echo json_encode(["error" => "Debe ingresar credenciales y contraseña."]);
        exit;
    }

    $sql = "SELECT c.idControl, c.contrasena, c.activo, c.intentosFallidos, e.idPuesto
            FROM credenciales c
            INNER JOIN empleado e ON c.idEmpleado = e.idEmpleado
            WHERE c.idControl = :credenciales";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':credenciales' => $credenciales]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(["error" => "Credenciales no encontradas."]);
        exit;
    }

    if ($user['activo'] !== 'Activo') {
        echo json_encode(["error" => "La cuenta está inactiva."]);
        exit;
    }

    if (!password_verify($password, $user['contrasena'])) {
        $nuevoIntento = $user['intentosFallidos'] + 1;
        $update = $conn->prepare("UPDATE credenciales SET intentosFallidos = :fallidos WHERE idControl = :credenciales");
        $update->execute([':fallidos' => $nuevoIntento, ':credenciales' => $credenciales]);
        echo json_encode(["error" => "Contraseña incorrecta. Intento #" . $nuevoIntento]);
        exit;
    }

    $reset = $conn->prepare("UPDATE credenciales SET intentosFallidos = 0 WHERE idControl = :credenciales");
    $reset->execute([':credenciales' => $credenciales]);

    $redirecciones = [
        1 => "credenciales.html",
        2 => "panel_venta.php",
        3 => "panel_almacen.php",
        4 => "panel_contador.php"
    ];

    $redirect = $redirecciones[$user['idPuesto']] ?? "panel_general.php";

    echo json_encode(["success" => true, "redirect" => $redirect]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error del servidor: " . $e->getMessage()]);
}
?>
