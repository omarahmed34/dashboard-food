<?php
require_once 'db.php';
try {
    $stmt = $pdo->query("SELECT * FROM orders");
    $rows = $stmt->fetchAll();
    echo "COUNT: " . count($rows) . "\n";
    print_r($rows);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
