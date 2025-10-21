<?php

header('Content-Type: application/json');
require_once 'connection.php';

try {
    $conn = ConexionDB::setConnection();
    $conn->beginTransaction();

    // 1. Recibir y validar datos
    $entrada = json_decode(file_get_contents('php://input'), true);
    $datos = $entrada['datosFormulario'] ?? [];

    // Validación de campos obligatorios
    if (empty($datos['nombre']) || empty($datos['contrasena']) || empty($datos['puesto']) || 
        empty($datos['apellidoPaterno']) || empty($datos['apellidoMaterno']) || empty($datos['telefono']) ||
        empty($datos['calle']) || empty($datos['numCalle']) || empty($datos['localidad']) || 
        empty($datos['codigoPostal'])) {
        
        http_response_code(400); 
        echo json_encode([
            "errorDB" => "Faltan campos obligatorios para el registro. Por favor, llene todos los campos requeridos."
        ]);
        exit;
    }
    
    // Validación de teléfono (10 dígitos)
    if (!preg_match('/^[0-9]{10}$/', $datos['telefono'])) {
        http_response_code(400);
        echo json_encode([
            "errorDB" => "El teléfono debe contener exactamente 10 dígitos numéricos."
        ]);
        exit;
    }
    
    // Validación de código postal (5 dígitos)
    if (!preg_match('/^[0-9]{5}$/', $datos['codigoPostal'])) {
        http_response_code(400);
        echo json_encode([
            "errorDB" => "El código postal debe contener exactamente 5 dígitos."
        ]);
        exit;
    }
    
    // Hash de contraseña
    $contrasenaPlana = $datos['contrasena'];
    $contrasenaHash = password_hash($contrasenaPlana, PASSWORD_DEFAULT);
    
    // Mapeo de puestos
    $puestoMap = [
        'gerente'   => 1,
        'venta'     => 2,
        'almacen'   => 3,
        'contador'  => 4
    ];
    $idPuesto = $puestoMap[strtolower($datos['puesto'])] ?? null;

    if ($idPuesto === null) {
        http_response_code(400);
        echo json_encode([
            "errorDB" => "Puesto de empleado inválido: " . $datos['puesto']
        ]);
        exit;
    }

    $now = date("Y-m-d H:i:s");
    $fechaCreacion = date("Y-m-d");
    
    // PASO A: INSERCIÓN EN TABLA DIRECCION
    $sqlDireccion = "INSERT INTO direccion (
        nombreCalle, numeroCalle, localidad, codigoPostal
    ) VALUES (
        :nombreCalle, :numeroCalle, :localidad, :codigoPostal
    )";
    $stmtDireccion = $conn->prepare($sqlDireccion);
    $stmtDireccion->execute([
        ':nombreCalle'  => trim($datos['calle']),
        ':numeroCalle'  => intval($datos['numCalle']),
        ':localidad'    => trim($datos['localidad']),
        ':codigoPostal' => $datos['codigoPostal']
    ]);
    $idDireccion = $conn->lastInsertId();
    
    // PASO B: INSERCIÓN EN TABLA EMPLEADO
    $sqlEmpleado = "INSERT INTO empleado (
        nombre, apellidoPaterno, apellidoMaterno, telefono, idPuesto, idDireccion
    ) VALUES (
        :nombre, :apPaterno, :apMaterno, :telefono, :idPuesto, :idDireccion
    )";
    $stmtEmpleado = $conn->prepare($sqlEmpleado);
    $stmtEmpleado->execute([
        ':nombre'      => trim($datos['nombre']),
        ':apPaterno'   => trim($datos['apellidoPaterno']),
        ':apMaterno'   => trim($datos['apellidoMaterno']),
        ':telefono'    => $datos['telefono'],
        ':idPuesto'    => $idPuesto,
        ':idDireccion' => $idDireccion
    ]);
    $idEmpleado = $conn->lastInsertId();

    // PASO C: INSERCIÓN EN TABLA CREDENCIALES
    $inicialPuesto = strtoupper(substr($datos['puesto'], 0, 1));
    $yearShort = date('y');
    $month = date('m');
    $day = date('d');
    $idEmpleadoPadded = str_pad($idEmpleado, 2, '0', STR_PAD_LEFT);
    $idControl = $inicialPuesto . $yearShort . $month . $day . $idEmpleadoPadded;
    
    $sqlCredencial = "INSERT INTO credenciales (
        idControl, idEmpleado, contrasena, intentosFallidos, fechaCreacion, ultimoCambio, activo
    ) VALUES (
        :idControl, :idEmpleado, :contrasena, :intentosFallidos, :fechaCreacion, :ultimoCambio, :activo
    )";
    $stmtCredencial = $conn->prepare($sqlCredencial);
    $stmtCredencial->execute([
        ':idControl'        => $idControl,
        ':idEmpleado'       => $idEmpleado,
        ':contrasena'       => $contrasenaHash,
        ':intentosFallidos' => 0,
        ':fechaCreacion'    => $fechaCreacion,
        ':ultimoCambio'     => $now,
        ':activo'           => 'Activo'
    ]);

    $conn->commit();
    
    // Éxito
    echo json_encode([
        "mensaje" => "Usuario registrado exitosamente.",
        "idInsertado" => $idEmpleado,
        "idControlGenerado" => $idControl,
        "creado" => true
    ]);

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    http_response_code(500);
    
    $mensajeError = "Error de base de datos.";
    
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        if (strpos($e->getMessage(), 'telefono') !== false) {
            $mensajeError = "El número de teléfono ya está registrado.";
        } else {
            $mensajeError = "Ya existe un registro con esos datos.";
        }
    } else {
        // En desarrollo, mostrar el error completo
        $mensajeError .= " Detalle: " . $e->getMessage();
    }
    
    echo json_encode(["errorDB" => $mensajeError]);
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        "errorServer" => "Error del servidor: " . $e->getMessage()
    ]);
}
?>