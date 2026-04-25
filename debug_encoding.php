<?php
require_once 'db.php';
header('Content-Type: text/plain; charset=utf-8');

try {
    echo "--- Recipes Data Sample ---\n";
    $stmt = $pdo->query("SELECT id, name, category FROM recipes LIMIT 5");
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['id']} | Name: {$row['name']} | Category: {$row['category']}\n";
    }

    echo "\n--- About Page Data Sample ---\n";
    $stmt = $pdo->query("SELECT * FROM about_page LIMIT 2");
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['id']} | Lang: {$row['lang']} | Title: {$row['title']}\n";
    }

    echo "\n--- Site Settings (if exists) ---\n";
    // Check if site table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'site'");
    if ($stmt->fetch()) {
        $stmt = $pdo->query("SELECT * FROM site LIMIT 5");
        while ($row = $stmt->fetch()) {
            print_r($row);
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
