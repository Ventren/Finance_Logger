<?php
require_once 'db.php';

try{
    $sql = "SELECT * FROM categories ORDER BY category ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $categories = $stmt->fetchAll();

    //Send to Vue as JSON
    echo json_encode($categories);

}catch(PDOException $e){
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>