<?php
header('Content-Type: application/json');
require_once 'connection.php';

try {
    $conn = ConexionDB::setConnection();
    
    $sql = "SELECT 
                c.idControl,
                c.idEmpleado,
                CONCAT(e.nombre, ' ', e.apellidoPaterno, ' ', IFNULL(e.apellidoMaterno, '')) as nombreCompleto,
                c.idControl as usuario,
                c.contrasena,
                DATE_FORMAT(c.fechaCreacion, '%d/%m/%Y') as fechaCreacion,
                DATE_FORMAT(c.ultimoCambio, '%d/%m/%Y') as ultimoCambio,
                c.activo,
                c.intentosFallidos
            FROM credenciales c
            INNER JOIN empleado e ON c.idEmpleado = e.idEmpleado
            LIMIT 3";
    
    $resultado = $conn->query($sql);
    $datos = $resultado->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($datos, JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    echo json_encode(["errorDB" => "Error: " . $e->getMessage()]);
}
?>