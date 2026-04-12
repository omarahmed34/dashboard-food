<?php
require 'c:\xampp\htdocs\HCI\db.php';

try {
    $pdo->query("SET NAMES utf8mb4");
    
    // Insert Users
    $pdo->exec("INSERT INTO users (name, email, avatar, join_date, saves, status) VALUES 
        ('أحمد سعيد', 'ahmed@email.com', 'https://i.pravatar.cc/100?img=11', '2026-04-01', 5, 'نشط')");

    // Insert Ingredients
    $pdo->exec("INSERT INTO ingredients (name, emoji, category, used_in) VALUES 
        ('طماطم', '🍅', 'خضروات', 1),
        ('بيض', '🥚', 'بروتين', 1)");
    $stmt = $pdo->query("SELECT id FROM ingredients ORDER BY id DESC LIMIT 2");
    $ingredients = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Insert Recipe
    $stmt = $pdo->prepare("INSERT INTO recipes (name, category, time, difficulty, status, image, steps, saves, views) VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        'عجة طماطم',
        'فطار',
        '10 دقيقة',
        'سهل',
        'نشطة',
        'https://images.unsplash.com/photo-1510693206972-df098062cb71',
        json_encode(['نقطع الطماطم', 'نكسر البيض', 'نقلب جيداً'], JSON_UNESCAPED_UNICODE),
        10,
        150
    ]);
    $recipeId = $pdo->lastInsertId();

    // Link Recipe Ingredients
    foreach ($ingredients as $ing_id) {
        $pdo->prepare("INSERT INTO recipe_ingredients (recipe_id, ingredient_id) VALUES (?, ?)")->execute([$recipeId, $ing_id]);
    }

    echo "Data seeded successfully!\n\n";

} catch (Exception $e) {
    echo "Error inserting data: " . $e->getMessage() . "\n";
}

// Fetch via API structure
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['action'] = 'getAllData';
ob_start();
require 'c:\xampp\htdocs\HCI\api.php';
$output = ob_get_clean();

echo "API Response: \n";
echo substr($output, 0, 500) . "..."; // print first 500 characters
?>
