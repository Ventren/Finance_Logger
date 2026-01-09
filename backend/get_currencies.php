<?php
require_once 'db.php';

try {
    $stmt = $conn->query("SELECT * FROM currencies ORDER BY code ASC");
    $currencies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($currencies);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>