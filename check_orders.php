<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$result = [];

// Check if orders table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    $result['orders_table_exists'] = ($stmt->rowCount() > 0);
} catch (Exception $e) {
    $result['orders_table_exists'] = 'ERROR: ' . $e->getMessage();
}

// Describe orders table if it exists
try {
    $stmt = $pdo->query("DESCRIBE orders");
    $result['orders_columns'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $result['orders_columns'] = 'ERROR: ' . $e->getMessage();
}

// Count rows
try {
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM orders");
    $result['orders_count'] = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $result['orders_count'] = 'ERROR: ' . $e->getMessage();
}

// Get sample rows
try {
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");
    $result['orders_sample'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $result['orders_sample'] = 'ERROR: ' . $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
