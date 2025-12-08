<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'connection.php';

try {
    $conn = ConexionDB::setConnection();
    
    // ============================================
    // ENDPOINT: Obtener todos los empleados
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
        $mostrarInactivos = isset($_GET['inactivos']) && $_GET['inactivos'] === '1';
        
        $sql = "SELECT 
                    e.idEmpleado,
                    e.nombre,
                    e.apellidoPaterno,
                    e.apellidoMaterno,
                    e.telefono,
                    e.idPuesto,
                    p.puesto as nombrePuesto,
                    
                    e.idDireccion,
                    d.nombreCalle,
                    d.numeroCalle,
                    d.localidad,
                    d.codigoPostal,
                    c.idControl,
                    c.activo,
                    c.fechaCreacion
                FROM empleado e
                INNER JOIN puestoempleado p ON e.idPuesto = p.idPuesto
                INNER JOIN direccion d ON e.idDireccion = d.idDireccion
                LEFT JOIN credenciales c ON e.idEmpleado = c.idEmpleado";
        
        // Filtrar por estado activo/inactivo
        if (!$mostrarInactivos) {
            $sql .= " WHERE c.activo = 'Activo'";
        }
        
        // Agregar búsqueda
        if ($busqueda !== '') {
            $sql .= $mostrarInactivos ? " WHERE" : " AND";
            $sql .= " (e.nombre LIKE ? 
                      OR e.apellidoPaterno LIKE ? 
                      OR e.apellidoMaterno LIKE ?
                      OR e.telefono LIKE ?
                      OR p.puesto LIKE ?
                      OR c.idControl LIKE ?)";
        }
        
        $sql .= " ORDER BY c.activo DESC, e.nombre ASC";
        
        $stmt = $conn->prepare($sql);
        
        if ($busqueda !== '') {
            $busquedaParam = "%{$busqueda}%";
            $stmt->execute([$busquedaParam, $busquedaParam, $busquedaParam, $busquedaParam, $busquedaParam, $busquedaParam]);
        } else {
            $stmt->execute();
        }
        
        $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $empleados,
            'total' => count($empleados)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // ============================================
    // ENDPOINT: Actualizar empleado (PUT)
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar campos requeridos
        if (!isset($data['idEmpleado']) || !isset($data['nombre']) || !isset($data['apellidoPaterno']) || 
            !isset($data['apellidoMaterno']) || !isset($data['telefono'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Faltan datos obligatorios'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $idEmpleado = intval($data['idEmpleado']);
        $nombre = trim($data['nombre']);
        $apellidoPaterno = trim($data['apellidoPaterno']);
        $apellidoMaterno = trim($data['apellidoMaterno']);
        $telefono = trim($data['telefono']);
        $activo = isset($data['activo']) ? trim($data['activo']) : 'Activo';
        
        // Datos de dirección
        $nombreCalle = trim($data['nombreCalle']);
        $numeroCalle = intval($data['numeroCalle']);
        $localidad = trim($data['localidad']);
        $codigoPostal = trim($data['codigoPostal']);
        $idDireccion = intval($data['idDireccion']);
        
        // ============================================
        // VALIDACIONES
        // ============================================
        
        // Validar que el empleado exista
        $stmt = $conn->prepare("SELECT idEmpleado FROM empleado WHERE idEmpleado = ?");
        $stmt->execute([$idEmpleado]);
        if ($stmt->rowCount() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'El empleado no existe'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Validar nombre
        if (strlen($nombre) < 2 || strlen($nombre) > 30) {
            echo json_encode([
                'success' => false,
                'message' => 'El nombre debe tener entre 2 y 30 caracteres'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\'\\s]+$/', $nombre)) {
            echo json_encode([
                'success' => false,
                'message' => 'El nombre solo puede contener letras'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Validar apellidos
        if (strlen($apellidoPaterno) < 2 || strlen($apellidoPaterno) > 30) {
            echo json_encode([
                'success' => false,
                'message' => 'El apellido paterno debe tener entre 2 y 30 caracteres'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\'\\s]+$/', $apellidoPaterno)) {
            echo json_encode([
                'success' => false,
                'message' => 'El apellido paterno solo puede contener letras'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        if (strlen($apellidoMaterno) < 2 || strlen($apellidoMaterno) > 30) {
            echo json_encode([
                'success' => false,
                'message' => 'El apellido materno debe tener entre 2 y 30 caracteres'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\'\\s]+$/', $apellidoMaterno)) {
            echo json_encode([
                'success' => false,
                'message' => 'El apellido materno solo puede contener letras'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Validar teléfono
        if (!preg_match('/^[0-9]{10}$/', $telefono)) {
            echo json_encode([
                'success' => false,
                'message' => 'El teléfono debe tener exactamente 10 dígitos'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Verificar si el teléfono ya existe en otro empleado
        $stmt = $conn->prepare("SELECT idEmpleado FROM empleado WHERE telefono = ? AND idEmpleado != ?");
        $stmt->execute([$telefono, $idEmpleado]);
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'El teléfono ya está registrado en otro empleado'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Validar código postal
        if (!preg_match('/^[0-9]{5}$/', $codigoPostal)) {
            echo json_encode([
                'success' => false,
                'message' => 'El código postal debe tener exactamente 5 dígitos'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Validar estado
        if (!in_array($activo, ['Activo', 'Baja'])) {
            echo json_encode([
                'success' => false,
                'message' => 'El estado debe ser "Activo" o "Baja"'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // ============================================
        // ACTUALIZAR DATOS
        // ============================================
        $conn->beginTransaction();
        
        try {
            // Actualizar empleado
            $sqlEmpleado = "UPDATE empleado 
                            SET nombre = ?, apellidoPaterno = ?, apellidoMaterno = ?, telefono = ?
                            WHERE idEmpleado = ?";
            $stmtEmpleado = $conn->prepare($sqlEmpleado);
            $stmtEmpleado->execute([$nombre, $apellidoPaterno, $apellidoMaterno, $telefono, $idEmpleado]);
            
            // Actualizar dirección
            $sqlDireccion = "UPDATE direccion 
                             SET nombreCalle = ?, numeroCalle = ?, localidad = ?, codigoPostal = ?
                             WHERE idDireccion = ?";
            $stmtDireccion = $conn->prepare($sqlDireccion);
            $stmtDireccion->execute([$nombreCalle, $numeroCalle, $localidad, $codigoPostal, $idDireccion]);
            
            // Actualizar estado en credenciales
            $sqlCredenciales = "UPDATE credenciales 
                               SET activo = ?, ultimoCambio = CURDATE()
                               WHERE idEmpleado = ?";
            $stmtCredenciales = $conn->prepare($sqlCredenciales);
            $stmtCredenciales->execute([$activo, $idEmpleado]);
            
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Empleado actualizado exitosamente'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
    
    // ============================================
    // ENDPOINT: Eliminar empleado (DELETE)
    // ============================================
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['idEmpleado'])) {
            echo json_encode([
                'success' => false,
                'message' => 'El ID del empleado es obligatorio'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $idEmpleado = intval($data['idEmpleado']);
        
        // Verificar que el empleado exista
        $stmt = $conn->prepare("SELECT idEmpleado FROM empleado WHERE idEmpleado = ?");
        $stmt->execute([$idEmpleado]);
        if ($stmt->rowCount() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'El empleado no existe'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // ============================================
        // ELIMINACIÓN EN CASCADA COMPLETA
        // ============================================
        $conn->beginTransaction();
        
        try {
            // 1. Obtener IDs de ingresos relacionados a las ventas del empleado
            $sqlIngresos = "SELECT DISTINCT v.idIngreso 
                            FROM venta v 
                            WHERE v.idEmpleado = ? AND v.idIngreso IS NOT NULL";
            $stmtIngresos = $conn->prepare($sqlIngresos);
            $stmtIngresos->execute([$idEmpleado]);
            $ingresos = $stmtIngresos->fetchAll(PDO::FETCH_COLUMN);
            
            // 2. Eliminar empleado
            // El CASCADE automáticamente eliminará:
            // - credenciales (CASCADE)
            // - direccion (CASCADE)
            // - venta (CASCADE)
            // - productoventa (CASCADE desde venta)
            $sqlEmpleado = "DELETE FROM empleado WHERE idEmpleado = ?";
            $stmtEmpleado = $conn->prepare($sqlEmpleado);
            $stmtEmpleado->execute([$idEmpleado]);
            
            // 3. Eliminar ingresos (esto NO se hace automáticamente)
            if (!empty($ingresos)) {
                $placeholders = str_repeat('?,', count($ingresos) - 1) . '?';
                $sqlDeleteIngresos = "DELETE FROM ingreso WHERE idIngreso IN ($placeholders)";
                $stmtDeleteIngresos = $conn->prepare($sqlDeleteIngresos);
                $stmtDeleteIngresos->execute($ingresos);
            }
            
            $conn->commit();
            
            $cantidadIngresos = count($ingresos);
            $mensaje = 'Empleado eliminado exitosamente';
            if ($cantidadIngresos > 0) {
                $mensaje .= " (Se eliminaron {$cantidadIngresos} ingreso(s) relacionado(s))";
            }
            
            echo json_encode([
                'success' => true,
                'message' => $mensaje
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar el empleado: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>