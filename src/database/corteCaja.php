<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'connection.php';

// 📝 Función para escribir logs
function escribirLog($mensaje) {
    $log = date('Y-m-d H:i:s') . " - " . $mensaje . "\n";
    file_put_contents('corte_debug.log', $log, FILE_APPEND);
}

try {
    $pdo = ConexionDB::setConnection();
    
    // Leer el cuerpo de la petición
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    escribirLog("=== INICIO CORTE DE CAJA ===");
    escribirLog("Datos recibidos: " . json_encode($data));
    
    if (isset($data['action']) && $data['action'] === 'cerrarDia') {
        
        // Obtener la fecha del parámetro o usar la fecha actual
        $fechaHoy = isset($data['fecha']) ? $data['fecha'] : date('Y-m-d');
        escribirLog("Fecha a cerrar: " . $fechaHoy);
        
        // Crear tabla bloqueos_sesion si no existe
        $createBloqueos = "
            CREATE TABLE IF NOT EXISTS bloqueos_sesion (
                idBloqueo INT AUTO_INCREMENT PRIMARY KEY,
                fechaBloqueo DATE NOT NULL,
                activo TINYINT(1) DEFAULT 1,
                fechaCreacion DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY fecha_bloqueo_unica (fechaBloqueo),
                INDEX idx_fecha_activo (fechaBloqueo, activo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        $pdo->exec($createBloqueos);
        escribirLog("✅ Tabla bloqueos_sesion verificada/creada");
        
        // Crear tabla corte_caja si no existe
        $createTable = "
            CREATE TABLE IF NOT EXISTS corte_caja (
                idCorte INT AUTO_INCREMENT PRIMARY KEY,
                fecha DATE NOT NULL,
                totalDia DECIMAL(10,2) NOT NULL,
                efectivo DECIMAL(10,2) NOT NULL,
                tarjeta DECIMAL(10,2) NOT NULL,
                ventasRealizadas INT NOT NULL,
                productosVendidos INT NOT NULL,
                empleadosActivos INT NOT NULL,
                fechaCierre DATETIME NOT NULL,
                idEmpleadoCierre INT NULL,
                UNIQUE KEY fecha_unica (fecha),
                INDEX idx_fecha (fecha)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        $pdo->exec($createTable);
        escribirLog("✅ Tabla corte_caja verificada/creada");
        
        // Verificar si ya se cerró el día
        $stmtVerificar = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM corte_caja
            WHERE fecha = :fecha
        ");
        $stmtVerificar->execute(['fecha' => $fechaHoy]);
        $resultado = $stmtVerificar->fetch();
        
        if ($resultado['total'] > 0) {
            escribirLog("⚠️ El día ya fue cerrado anteriormente");
            echo json_encode([
                'success' => false,
                'message' => 'El día ya fue cerrado anteriormente'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Obtener los datos del resumen
        $stmtTotal = $pdo->prepare("
            SELECT COALESCE(SUM(importeTotal), 0) as totalDia
            FROM ingreso
            WHERE fecha = :fecha
        ");
        $stmtTotal->execute(['fecha' => $fechaHoy]);
        $totalDia = $stmtTotal->fetch()['totalDia'];
        
        $stmtEfectivo = $pdo->prepare("
            SELECT COALESCE(SUM(importeTotal), 0) as efectivo
            FROM ingreso
            WHERE fecha = :fecha AND metodoPago = 'efectivo'
        ");
        $stmtEfectivo->execute(['fecha' => $fechaHoy]);
        $efectivo = $stmtEfectivo->fetch()['efectivo'];
        
        $stmtTarjeta = $pdo->prepare("
            SELECT COALESCE(SUM(importeTotal), 0) as tarjeta
            FROM ingreso
            WHERE fecha = :fecha AND metodoPago = 'tarjeta'
        ");
        $stmtTarjeta->execute(['fecha' => $fechaHoy]);
        $tarjeta = $stmtTarjeta->fetch()['tarjeta'];
        
        $stmtVentas = $pdo->prepare("
            SELECT COUNT(*) as ventasRealizadas
            FROM venta
            WHERE fechaVenta = :fecha
        ");
        $stmtVentas->execute(['fecha' => $fechaHoy]);
        $ventasRealizadas = $stmtVentas->fetch()['ventasRealizadas'];
        
        $stmtProductos = $pdo->prepare("
            SELECT COALESCE(SUM(pv.cantidad), 0) as productosVendidos
            FROM productoVenta pv
            INNER JOIN venta v ON pv.idVenta = v.idVenta
            WHERE v.fechaVenta = :fecha
        ");
        $stmtProductos->execute(['fecha' => $fechaHoy]);
        $productosVendidos = $stmtProductos->fetch()['productosVendidos'];
        
        $stmtEmpleados = $pdo->prepare("
            SELECT COUNT(DISTINCT idEmpleado) as empleadosActivos
            FROM venta
            WHERE fechaVenta = :fecha
        ");
        $stmtEmpleados->execute(['fecha' => $fechaHoy]);
        $empleadosActivos = $stmtEmpleados->fetch()['empleadosActivos'];
        
        escribirLog("Resumen - Total: $totalDia, Ventas: $ventasRealizadas");
        
        // Obtener ID del empleado que cierra (de la sesión)
        $idEmpleadoCierre = isset($_SESSION['usuario']['idEmpleado']) ? $_SESSION['usuario']['idEmpleado'] : null;
        escribirLog("Empleado que cierra: " . ($idEmpleadoCierre ?? 'NULL'));
        
        // Insertar el corte de caja
        $stmtInsert = $pdo->prepare("
            INSERT INTO corte_caja 
            (fecha, totalDia, efectivo, tarjeta, ventasRealizadas, productosVendidos, empleadosActivos, fechaCierre, idEmpleadoCierre)
            VALUES 
            (:fecha, :totalDia, :efectivo, :tarjeta, :ventasRealizadas, :productosVendidos, :empleadosActivos, NOW(), :idEmpleadoCierre)
        ");
        
        $stmtInsert->execute([
            'fecha' => $fechaHoy,
            'totalDia' => $totalDia,
            'efectivo' => $efectivo,
            'tarjeta' => $tarjeta,
            'ventasRealizadas' => $ventasRealizadas,
            'productosVendidos' => $productosVendidos,
            'empleadosActivos' => $empleadosActivos,
            'idEmpleadoCierre' => $idEmpleadoCierre
        ]);
        escribirLog("✅ Corte de caja registrado");
        
        // 🔒 REGISTRAR BLOQUEO DE SESIÓN PARA HOY
        try {
            $stmtBloqueo = $pdo->prepare("
                INSERT INTO bloqueos_sesion (fechaBloqueo, activo)
                VALUES (:fecha, 1)
                ON DUPLICATE KEY UPDATE activo = 1
            ");
            $stmtBloqueo->execute(['fecha' => $fechaHoy]);
            escribirLog("✅ Bloqueo de sesión registrado para: " . $fechaHoy);
            
            // Verificar que se registró
            $stmtCheck = $pdo->prepare("SELECT * FROM bloqueos_sesion WHERE fechaBloqueo = :fecha");
            $stmtCheck->execute(['fecha' => $fechaHoy]);
            $bloqueoRegistrado = $stmtCheck->fetch();
            escribirLog("Verificación bloqueo: " . json_encode($bloqueoRegistrado));
            
        } catch (PDOException $e) {
            escribirLog("⚠️ Error al registrar bloqueo: " . $e->getMessage());
        }
        
        // 🚪 DESTRUIR SESIÓN DEL USUARIO
        $usuarioAntes = isset($_SESSION['usuario']) ? $_SESSION['usuario']['nombreCompleto'] : 'Desconocido';
        escribirLog("Cerrando sesión de: " . $usuarioAntes);
        
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        escribirLog("✅ Sesión destruida");
        
        echo json_encode([
            'success' => true,
            'message' => '✅ El día se cerró correctamente. Tu sesión ha sido cerrada.',
            'sesionCerrada' => true,
            'corte' => [
                'fecha' => $fechaHoy,
                'totalDia' => floatval($totalDia),
                'efectivo' => floatval($efectivo),
                'tarjeta' => floatval($tarjeta),
                'ventasRealizadas' => intval($ventasRealizadas),
                'productosVendidos' => intval($productosVendidos),
                'empleadosActivos' => intval($empleadosActivos)
            ]
        ], JSON_UNESCAPED_UNICODE);
        
        escribirLog("=== CIERRE EXITOSO ===");
        
    } else {
        escribirLog("ERROR: Acción no válida");
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (PDOException $e) {
    escribirLog("ERROR PDO: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    escribirLog("ERROR General: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>