<?php
require_once 'db.php';
$tables = ['recipes', 'ingredients', 'users', 'favorites', 'recipe_ingredients', 'dashlogen', 'contact', 'sine', 'about_page', 'categories'];
foreach ($tables as $t) {
    echo "--- $t ---\n";
    try {
        $stmt = $pdo->query("DESCRIBE `$t`");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo $c['Field'] . " (" . $c['Type'] . ")\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
