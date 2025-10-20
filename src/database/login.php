<?php
// updateCredenciales.php - VERSIÓN CORREGIDA E INTEGRADA
header('Content-Type: application/json');
require_once 'Conexion.php'; // Corregido: usa el mismo archivo que el login

try {
    $conn = ConexionDB::setConnection();

    // 1. Recibir datos del frontend (JSON)
    $entrada = json_decode(file_get_contents('php://input'), true);
    $datosFormulario = $entrada['datosFormulario'] ?? [];

    // 2. Validar datos requeridos
    if (empty($datosFormulario['idEmpleado'])) {
        http_response_code(400);
        echo json_encode(["errorDB" => "ID de empleado no proporcionado."]);
        exit;
    }

    $idEmpleado = $datosFormulario['idEmpleado'];
    $contrasenaPlana = $datosFormulario['contrasena'] ?? '';
    $activo = $datosFormulario['activo'] ?? 'Activo'; // Default a 'Activo'
    $intentosFallidos = (int)($datosFormulario['intentosFallidos'] ?? 0);
    $ultimoCambio = date("Y-m-d H:i:s");

    // Validar 'activo' (solo 'Activo' o 'Baja')
    if (!in_array($activo, ['Activo', 'Baja'])) {
        http_response_code(400);
        echo json_encode(["errorDB" => "Valor de 'activo' inválido. Debe ser 'Activo' o 'Baja'."]);
        exit;
    }

    // Validar intentosFallidos (número entre 0 y 3)
    if ($intentosFallidos < 0 || $intentosFallidos > 3) {
        http_response_code(400);
        echo json_encode(["errorDB" => "Intentos fallidos debe ser un número entre 0 y 3."]);
        exit;
    }

    // 3. Hashear la contraseña (consistente con el login)
    $contrasenaHashed = password_hash($contrasenaPlana, PASSWORD_DEFAULT);

    // 4. Preparar consulta SQL (usa 'password' en lugar de 'contrasena' para consistencia)
    $sql = "UPDATE credenciales 
            SET password = :password,  -- Cambiado a 'password' (hashed)
                activo = :activo, 
                intentos_fallidos = :intentos_fallidos,  -- Cambiado a snake_case
                ultimoCambio = :ultimoCambio 
            WHERE idEmpleado = :idEmpleado";  -- Asumiendo que 'idEmpleado' es la FK correcta
            
    $stmt = $conn->prepare($sql);

    // 5. Ejecutar actualización
    $stmt->execute([
        ':password' => $contrasenaHashed,
        ':activo' => $activo,
        ':intentos_fallidos' => $intentosFallidos,
        ':ultimoCambio' => $ultimoCambio,
        ':idEmpleado' => $idEmpleado
    ]);

    // 6. Verificar resultado
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            "mensaje" => "Credencial actualizada exitosamente", 
            "actualizado" => true, 
            "ultimoCambio" => date('d/m/Y', strtotime($ultimoCambio))
        ]);
    } else {
        echo json_encode([
            "errorDB" => "No se realizó actualización. Verifica el ID o que los datos sean diferentes."
        ]);
    }

} catch (PDOException $e) {
    error_log("Error en updateCredenciales: " . $e->getMessage()); // Logging como en login
    http_response_code(500);
    echo json_encode(["errorDB" => "Error de base de datos: " . $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error general en updateCredenciales: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["errorServer" => "Error del servidor: " . $e->getMessage()]);
}
?>