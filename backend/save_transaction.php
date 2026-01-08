<?php
require_once 'db.php';

// to get the json sent from Vue.js because this is not usual PHP POST method 
$json = file_get_contents('php://input');
// we have to decode the json
$data = json_decode($json, true);

if (!$data || !isset($data['amount']) || !isset($data['category_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}
try{
$sql = "INSERT INTO transactions (category_id, `description`, amount, currency, transaction_date) 
        VALUES (:cat_id,:desc,:amount,:curr,:tran_date)";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ':cat_id' => $data['category_id'],
    ':desc' => $data['description'] ?? '',
    ':amount' => $data['amount'],
    ':curr' => $data['currency'] ?? 'MYR',
    ':tran_date' => $data['transaction_date'] ?? date('Y-m-d')
]);
echo json_encode(["success" => true, "message" => "Transaction Saved!"]) ;       

} catch(PDOException $e){
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>