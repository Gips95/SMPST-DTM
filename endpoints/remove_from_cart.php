<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['file_id'])) {
    $archivo_id = intval($_POST['file_id']);
    if (($key = array_search($archivo_id, $_SESSION['cart'])) !== false) {
        unset($_SESSION['cart'][$key]);
    }
    echo json_encode(['success' => true]);
    exit;
}