<?php 
require_once 'db.php';

try {
    // JOIN table(categories on transactions)
    $sql = "SELECT t.*, c.category,c.type 
            FROM transactions t 
            JOIN categories c ON t.category_id = c.id 
            ORDER BY t.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $transactions = $stmt->fetchAll();

    echo json_encode($transactions);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);

}
?>