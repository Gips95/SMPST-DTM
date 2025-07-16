<?php
include '../db/conn.php';
include_once('../classes/Users.class.php');
include_once('../classes/RPWcodes.class.php');
include_once('../classes/Logs.class.php');

session_start();

header('Content-Type: application/json');

// Verifica si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Captura y decodifica el JSON
$data = json_decode(file_get_contents("php://input"), true);

// Verifica si se envió el ID
if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no especificado']);
    exit;
}

$user_id = $data['id'];

$conexion->begin_transaction();
try {
    $user = User::getUser($user_id, $conexion);

    if (!$user) {
        throw new Exception('Usuario no encontrado');
    }
// 1. Eliminar reportes
$stmt = $conexion->prepare("DELETE FROM reportes WHERE autor = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// 2. Eliminar solicitudes de descarga donde él es el usuario
$stmt = $conexion->prepare("SELECT id FROM download_requests WHERE user_id = ? OR admin_id = ?");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$request_ids = [];
while ($row = $result->fetch_assoc()) {
    $request_ids[] = $row['id'];
}
$stmt->close();

// 2. Eliminar download_request_items relacionados a esas solicitudes
if (!empty($request_ids)) {
    $placeholders = implode(',', array_fill(0, count($request_ids), '?'));
    $types = str_repeat('i', count($request_ids));

    $stmt = $conexion->prepare("DELETE FROM download_request_items WHERE request_id IN ($placeholders)");
    $stmt->bind_param($types, ...$request_ids);
    $stmt->execute();
    $stmt->close();
}

// 3. Eliminar las download_requests ahora que los items fueron eliminados
$stmt = $conexion->prepare("DELETE FROM download_requests WHERE user_id = ? OR admin_id = ?");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$stmt->close();

// 3. Eliminar tokens RPW
$stmt = $conexion->prepare("DELETE FROM rpwtokens WHERE id_usuario = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

$stmt = $conexion->prepare("DELETE FROM requests WHERE id_element = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

    RPWcode::DeleteRPWcodes($user['id'], $conexion);
    User::DeleteUser($user['id'], $conexion);
    Log::CreateLog('delete', 'usuarios', $user['id'], $_SESSION['user'], $conexion, null, $user, null);

    $conexion->commit();
    $conexion->close();

    echo json_encode(['success' => true, 'message' => 'Usuario y datos eliminados correctamente']);
} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
