<?php
require_once 'db.php';

header('Content-Type: text/plain; charset=utf-8');
echo "--- فحص وإصلاح جدول الوصفات ---\n\n";

try {
    // 1. Check if 'recipes' table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'recipes'");
    if ($stmt->rowCount() == 0) {
        die("❌ خطأ: جدول 'recipes' غير موجود. يرجى تشغيل ملف الإعداد أولاً.");
    }

    // 2. Define required columns and their definitions
    $required_columns = [
        'status'     => "ADD COLUMN `status` VARCHAR(20) DEFAULT 'نشطة'",
        'image'      => "ADD COLUMN `image` VARCHAR(500) DEFAULT NULL",
        'saves'      => "ADD COLUMN `saves` INT DEFAULT 0",
        'views'      => "ADD COLUMN `views` INT DEFAULT 0",
        'difficulty' => "ADD COLUMN `difficulty` VARCHAR(50) DEFAULT NULL",
        'time'       => "ADD COLUMN `time` VARCHAR(50) DEFAULT NULL",
        'steps'      => "ADD COLUMN `steps` TEXT DEFAULT NULL"
    ];

    // 3. Get existing columns
    $stmt = $pdo->query("DESCRIBE `recipes`");
    $existing_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 4. Add missing columns
    foreach ($required_columns as $col => $sql_part) {
        if (!in_array($col, $existing_columns)) {
            echo "⚙️ جاري إضافة العمود المفقود: $col...\n";
            $pdo->exec("ALTER TABLE `recipes` $sql_part");
            echo "✅ تم بنجاح.\n";
        } else {
            echo "✔️ العمود $col موجود بالفعل.\n";
        }
    }

    echo "\n🚀 قاعدة البيانات الآن جاهزة للعمل مع لوحة التحكم!";
} catch (Exception $e) {
    echo "❌ خطأ أثناء الإصلاح: " . $e->getMessage();
}
?>
