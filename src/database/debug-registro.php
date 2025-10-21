<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// 1. Test conexión
require_once __DIR__ . '/connection.php';

// 2. Simular datos de prueba
$datosPrueba = [
    'datosFormulario' => [
        'nombre' => 'Test',
        'apPaterno' => 'Usuario',
        'apMaterno' => 'Prueba',
        'telefono' => '9512345678',
        'puesto' => 'gerente',
        'calle' => 'Calle Test',
        'numCalle' => '123',
        'localidad' => 'Oaxaca',
        'codigoPostal' => '68000',
        'contrasena' => 'test123'
    ]
];

// 3. Probar inserción
try {
    $datos = $datosPrueba['datosFormulario'];
    
    // Generar ID
    $idControl = 'EMP-' . date('Y') . '-' . rand(1000, 9999);
    $contrasena = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
    
    // Insertar empleado
    $sql = "INSERT INTO empleados (idControl, nombre, apPaterno, apMaterno, telefono, puesto, calle, numCalle, localidad, codigoPostal) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error prepare: " . $conexion->error);
    }
    
    $stmt->bind_param("ssssssssss", 
        $idControl,
        $datos['nombre'],
        $datos['apPaterno'],
        $datos['apMaterno'],
        $datos['telefono'],
        $datos['puesto'],
        $datos['calle'],
        $datos['numCalle'],
        $datos['localidad'],
        $datos['codigoPostal']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Error execute: " . $stmt->error);
    }
    
    echo json_encode([
        'exito' => true,
        'mensaje' => 'Inserción exitosa',
        'idControl' => $idControl,
        'idEmpleado' => $conexion->insert_id
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'mensaje' => $e->getMessage()
    ]);
}
?>