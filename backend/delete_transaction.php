<?php
require_once 'db.php';
$data = json_decode(file_get_contents('php://input'),true);

if (isset($data['id'])) {
    $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
    $stmt->execute([$data['id']]);
    echo json_encode(["success" => true]);
}
?>