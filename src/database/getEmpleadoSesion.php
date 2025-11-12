<?php
session_start();
header('Content-Type: application/json');

try {
    // Verificar que el usuario esté autenticado
    if (!isset($_SESSION['usuario'])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "error" => "No hay sesión activa. Por favor, inicie sesión."
        ]);
        exit;
    }
    
    $usuario = $_SESSION['usuario'];
    
    // Retornar datos del empleado
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "empleado" => [
            "idEmpleado" => $usuario['idEmpleado'],
            "idControl" => $usuario['idControl'],
            "nombre" => $usuario['nombre'],
            "apellidoPaterno" => $usuario['apellidoPaterno'],
            "apellidoMaterno" => $usuario['apellidoMaterno'],
            "nombreCompleto" => $usuario['nombreCompleto'],
            "puesto" => $usuario['puesto'],
            "idPuesto" => $usuario['idPuesto']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error del servidor: " . $e->getMessage()
    ]);
}
?>