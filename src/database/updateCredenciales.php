<?php
// updateCredenciales.php - SOLO ACTUALIZA CONTRASEÑA
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
    
    // 3. Validar contraseña
    if (empty($datosFormulario['contrasena'])) {
        http_response_code(400);
        echo json_encode(["errorDB" => "Contraseña no proporcionada."]);
        exit;
    }

    $contrasenaPlana = $datosFormulario['contrasena'];
    
    // Validar que sea exactamente 4 dígitos numéricos
    if (!preg_match('/^\d{4}$/', $contrasenaPlana)) {
        http_response_code(400);
        echo json_encode(["errorDB" => "La contraseña debe ser exactamente 4 dígitos numéricos."]);
        exit;
    }
    
    // HASHEAR LA CONTRASEÑA
    $contrasenaHash = password_hash($contrasenaPlana, PASSWORD_DEFAULT);

    $ultimoCambio = date("Y-m-d H:i:s"); 

    // 4. Preparar consulta SQL - SOLO ACTUALIZA CONTRASEÑA Y FECHA
    $sql = "UPDATE credenciales 
            SET contrasena = :contrasena, 
                ultimoCambio = :ultimoCambio 
            WHERE idEmpleado = :idEmpleado";
            
    $stmt = $conn->prepare($sql);

    // 5. Ejecutar actualización
    $stmt->execute([
        ':contrasena' => $contrasenaHash, 
        ':ultimoCambio' => $ultimoCambio,
        ':idEmpleado' => $idEmpleado
    ]);

    // 6. Verificar resultado
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            "mensaje" => "Contraseña actualizada exitosamente", 
            "actualizado" => true, 
            "ultimoCambio" => date('d/m/Y', strtotime($ultimoCambio))
        ]);
    } else {
        echo json_encode([
            "errorDB" => "No se realizó actualización. Verifica el ID del empleado."
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