<?php
header ("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods:GET,POST,PUT,DELETE,OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$host = "localhost";
$dbname = "financial_logger";
$username = "root";
$pass = "";

try{
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $pass);

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
}catch(PDOException $e){
    die("Connection Failed: " . $e->getMessages());
}
?>