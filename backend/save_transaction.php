<?php
require_once 'db.php';
// convert string to assoc array
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['amount']) || !isset($data['category_id'])) {
    http_response_code(400);
    echo json_encode([
        "error" => "Missing required fields",
        "received_data" => $data, // This shows us what Vue sent
        "check_amount" => isset($data['amount']),
        "check_category" => isset($data['category_id'])
    ]);
    exit;
}

// Check if we are UPDATING (if ID exists) or INSERTING
$id = $data['id'] ?? null;

try {
    if ($id) {
        // if edit
        $sql = "UPDATE transactions SET 
                category_id = :cat_id, 
                description = :desc, 
                amount = :amount, 
                currency = :curr, 
                transaction_date = :tran_date 
                WHERE id = :id";
    } else {
        // if new
        $sql = "INSERT INTO transactions (category_id, description, amount, currency, transaction_date) 
                VALUES (:cat_id, :desc, :amount, :curr, :tran_date)";
    }

    $stmt = $conn->prepare($sql);
    
    // Prepare the parameters
    $params = [
        ':cat_id'    => $data['category_id'],
        ':desc'      => $data['description'] ?? '',
        ':amount'    => $data['amount'],
        ':curr'      => $data['currency'] ?? 'MYR',
        ':tran_date' => $data['transaction_date'] ?? date('Y-m-d')
    ];

    // If we are updating, we need to add the :id to the parameters array
    if ($id) {
        $params[':id'] = $id;
    }

    $stmt->execute($params);

    echo json_encode([
        "success" => true, 
        "message" => $id ? "Transaction Updated!" : "Transaction Saved!"
    ]);       

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>