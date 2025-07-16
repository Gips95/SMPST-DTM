    <?php
    session_start();
    header('Content-Type: application/json');
    
    require '../db/conn.php'; // Asegúrate que la ruta es correcta
    if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
        // If not authorized, send a 401 Unauthorized status code
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Acceso no autorizado']);
        exit(); // Stop script execution
    }
    try {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin')
        {
            throw new Exception('Acceso no autorizado', 401);
        }
    
        $stmt = $conexion->prepare(
            "SELECT COUNT(id) AS total 
             FROM download_requests 
             WHERE status = 'pendiente'"
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Error en la consulta', 500);
        }
        
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
    
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'total' => $data['total'] ?? 0
        ]);
    
    } catch (Exception $e) {
        http_response_code($e->getCode() ?: 500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }