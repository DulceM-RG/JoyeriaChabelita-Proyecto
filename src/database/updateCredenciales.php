<?php
// updateCredenciales.php - VERSIÓN CORREGIDA FINAL
header('Content-Type: application/json');
require_once 'connection.php'; 

try {
    $conn = ConexionDB::setConnection();

    // 1. Recibir datos del frontend
    $entrada = json_decode(file_get_contents('php://input'), true);
    $datosFormulario = $entrada['datosFormulario'] ?? [];

    // 2. Validar ID de empleado
    if (empty($datosFormulario['idEmpleado'])) {
        http_response_code(400);
        echo json_encode(["errorDB" => "ID de empleado no proporcionado."]);
        exit;
    }

    $idEmpleado = $datosFormulario['idEmpleado'];
    $contrasenaPlana = $datosFormulario['contrasena'];
    
    // ✅ HASHEAR LA CONTRASEÑA (SÓLO UNA VEZ, AQUÍ)
    $contrasenaHash = password_hash($contrasenaPlana, PASSWORD_DEFAULT);
    // -------------------------------------------------------------------

    // Asignar los demás datos
    $activo = $datosFormulario['activo'];
    $intentosFallidos = $datosFormulario['intentosFallidos'];
    $ultimoCambio = date("Y-m-d H:i:s"); 

    // 3. Preparar consulta SQL
    $sql = "UPDATE credenciales 
            SET contrasena = :contrasena, 
                activo = :activo, 
                intentosFallidos = :intentosFallidos,
                ultimoCambio = :ultimoCambio 
            WHERE idEmpleado = :idEmpleado";
            
    $stmt = $conn->prepare($sql);

    // 4. Ejecutar actualización
    $stmt->execute([
        // PASAR EL HASH DE LA CONTRASEÑA A LA CONSULTA
        ':contrasena' => $contrasenaHash, 
        ':activo' => $activo,
        ':intentosFallidos' => $intentosFallidos,
        ':ultimoCambio' => $ultimoCambio,
        ':idEmpleado' => $idEmpleado
    ]);

    // 5. Verificar resultado
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
    http_response_code(500);
    echo json_encode(["errorDB" => "Error de base de datos: " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["errorServer" => "Error del servidor: " . $e->getMessage()]);
}
?>