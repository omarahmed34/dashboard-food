<?php
require_once 'db.php';

header('Content-Type: text/plain; charset=utf-8');
echo "--- عملية التنظيف الشاملة للمشاكل المستعصية ---\n";

try {
    // 1. حذف الجداول القديمة تماماً للتخلص من أي ترميز خاطئ
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $tables = ['recipes', 'ingredients', 'recipe_ingredients', 'users', 'sine', 'site', 'about_page', 'dashlogen', 'favorites', 'meal_planner', 'faq', 'shopping_list'];
    foreach ($tables as $t) {
        $pdo->exec("DROP TABLE IF EXISTS `$t` COLLATE utf8mb4_unicode_ci;");
    }
    echo "1. تم حذف جميع الجداول القديمة بنجاح.\n";

    // 2. إعادة إنشاء الجداول بترميز utf8mb4 الصريح في الكود
    $pdo->exec("CREATE TABLE `recipes` (
        `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` varchar(255) NOT NULL,
        `category` text DEFAULT NULL,
        `time` varchar(50) DEFAULT NULL,
        `difficulty` varchar(50) DEFAULT NULL,
        `status` varchar(20) DEFAULT 'نشطة',
        `image` varchar(500) DEFAULT NULL,
        `steps` text DEFAULT NULL,
        `saves` int(11) DEFAULT 0,
        `views` int(11) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    
    echo "2. تم إنشاء جدول Recipes بترميز utf8mb4_unicode_ci.\n";

    // 3. إدخال بيانات تجريبية عبر الكود البرمجي مباشرة (أضمن طريقة للعربي)
    $stmt = $pdo->prepare("INSERT INTO recipes (name, category, time, difficulty, steps) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        'عجة بالبيض والطماطم الصحيحة', 
        'إفطار شرقي', 
        '15 دقيقة', 
        'سهل للغاية', 
        'يتم تقطيع الطماطم|يتم خفق البيض|يُقدم ساخناً'
    ]);
    echo "3. تم إدخال وصفة تجريبية باللغة العربية.\n";

    echo "\n--- انتهينا! ---\n";
    echo "افتح لوحة التحكم الآن (Dashboard). \n";
    echo "إذا ظهرت 'عجة بالبيض والطماطم الصحيحة' بشكل سليم، فهذا يعني أن المشكلة حُلت.\n";
    echo "بعدها يمكنك تشغيل import_sql.php مرة أخيرة.";

} catch (Exception $e) {
    echo "❌ خطأ فادح: " . $e->getMessage();
}
?>
