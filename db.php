<?php
// Database Configuration
$host = "127.0.0.1"; // Using 127.0.0.1 instead of localhost for faster connection on Windows
$dbname = "bitesight_db";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "status" => "error", 
        "message" => "خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage()
    ]);
    exit;
}