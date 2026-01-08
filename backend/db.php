<?php
header ("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods:GET,POST,PUT,DELETE,OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
// preflight 'OPTIONS' request method solve the issue saving a transaction 
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = "localhost";
$dbname = "financial_logger";
$username = "root";
$pass = "";

try{
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $pass);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
}catch(PDOException $e){
    die("Connection Failed: " . $e->getMessage());
}
?>