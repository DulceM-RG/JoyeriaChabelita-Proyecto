<?php
/* establecer la conexión a la BD */
/*Clase ConexionDB y PDO */
class ConexionDB {
    /*clase estática llamada ConexionDB */
    public static function setConnection() {
        /*variables para la conexión a la base de datos */

        $host = "127.0.0.1"; // usa IP en lugar de localhost para evitar problemas
        $dbName = "joyeriachabelitaproy";
        $user = "root";
        $password = "root";
        $characterSet = "utf8mb4";
        $port = 3306;

        $dsn = "mysql:host=$host;dbname=$dbName;charset=$characterSet;port=$port";

        $options = [
            /* configura el comportamiento de la conexión PDO  */
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

             /*PDO lance una excepción (error) cada vez que ocurre un error en la base de datos. */
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            /*Establece el modo de obtención de resultados por defecto. Cuando ejecutas una consulta, los resultados se devolverán como arrays asociativos 
            (usando los nombres de las columnas como claves). */
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $password, $options);
            return $pdo;
        } catch (\PDOException $e) {
            throw new \PDOException(
                "Error de conexión de la base de datos: " . $e->getMessage(),
                (int)$e->getCode()
            );
        }
    }
}
?>
