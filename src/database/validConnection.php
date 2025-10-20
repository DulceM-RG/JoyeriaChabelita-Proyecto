<?php

header('Content-Type: application/json'); 
/* Probar la conexión a una base de datos y devolver el resultado en formato JSON */
require_once 'connection.php'; 
/*Incluye y evalúa el archivo llamado connection.php
Conection : clase o función para establecer la conexión a la base de datos
*/

try {    
    $conn=ConexionDB::setConnection();
    /* Llama a un método estático (setConnection()) de una clase llamada ConexionDB (que debe estar definida en connection.php
    Esta es la línea que intenta establecer la conexión real a la base de datos (por ejemplo, usando PDO o MySQLi). El resultado de la conexión se almacena en la variable $conn. */
    echo json_encode(["estadoDB" => "Conexión exitosa!!"]); 

} catch (Exception $e) {
    echo json_encode(["errorDB" => $e->getMessage()]); 
}
?>