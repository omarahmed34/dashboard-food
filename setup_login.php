<?php
/**
 * setup_login.php
 * Run ONCE via browser: http://localhost/HCI/setup_login.php
 * Creates the dashlogen table and inserts a default admin account.
 */
require_once 'db.php';

$results = [];

// 1 ─ Create dashlogen table if not exists
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `dashlogen` (
        `id`         INT AUTO_INCREMENT PRIMARY KEY,
        `name`       VARCHAR(100)  NOT NULL DEFAULT 'Admin',
        `email`      VARCHAR(150)  NOT NULL UNIQUE,
        `password`   VARCHAR(255)  NOT NULL,
        `role`       VARCHAR(50)   NOT NULL DEFAULT 'admin',
        `created_at` TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
$results[] = "✅ Table `dashlogen` created (or already exists)";

// 2 ─ Insert default admin if table is empty
$count = $pdo->query("SELECT COUNT(*) FROM dashlogen")->fetchColumn();
if ($count == 0) {
    $pdo->prepare("
        INSERT INTO dashlogen (name, email, password, role)
        VALUES (?, ?, ?, ?)
    ")->execute(['Chef Admin', 'oa2171770@gmail.com', '123123123', 'admin']);
    $results[] = "✅ Default admin inserted: oa2171770@gmail.com / 123123123 (Plaintext)";
} else {
    $results[] = "ℹ️ Table already has $count user(s) — skipped insert";
}

// Output
header('Content-Type: text/plain; charset=utf-8');
echo implode("\n", $results) . "\n\nDone! You can now delete this file.";
?>