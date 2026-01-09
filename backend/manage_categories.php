<?php
require_once 'db.php';
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

try {
    if ($method === 'POST') {
        $stmt = $conn->prepare("INSERT INTO categories (category,`type`) VALUES (:name,:type)");
        $stmt->execute([':name' => $data['category'],
                        ':type' => $data['type']]);
        echo json_encode(["success" => true]);
    } 
    elseif ($method === 'DELETE') {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute([':id' => $data['id']]);
        echo json_encode(["success" => true]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}