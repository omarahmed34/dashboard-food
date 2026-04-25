<?php
require_once 'db.php';

header('Content-Type: text/plain; charset=utf-8');
echo "--- تشخيص قاعدة البيانات ---\n";

try {
    // 1. عرض اسم قاعدة البيانات المتصل بها
    $db_name_result = $pdo->query("SELECT DATABASE()")->fetchColumn();
    echo "قاعدة البيانات الحالية: " . $db_name_result . "\n";

    // 2. عرض قائمة الجداول الموجودة فعلياً
    echo "الجداول الموجودة حالياً:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "[!] لا توجد أي جداول في قاعدة البيانات هذه.\n";
    } else {
        foreach ($tables as $table) {
            echo "- $table\n";
        }
    }

    // 3. محاولة إنشاء جدول recipe_ingredients للتجربة
    echo "\n--- محاولة الإصلاح التلقائي ---\n";
    $sql = "CREATE TABLE IF NOT EXISTS `recipe_ingredients` (
        `recipe_id` int(11) NOT NULL,
        `ingredient_id` int(11) NOT NULL,
        PRIMARY KEY (`recipe_id`,`ingredient_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    echo "تم إرسال أمر إنشاء جدول recipe_ingredients.\n";

    // 4. التحقق مرة أخرى
    $stmt = $pdo->query("SHOW TABLES");
    $tables_after = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('recipe_ingredients', $tables_after)) {
        echo "✅ تم إنشاء الجدول بنجاح الآن!\n";
    } else {
        echo "❌ لم يتم إنشاء الجدول، قد تكون هناك مشكلة في الصلاحيات.\n";
    }

} catch (Exception $e) {
    echo "❌ خطأ فني: " . $e->getMessage();
}
?>
