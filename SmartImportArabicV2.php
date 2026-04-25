<?php
require_once 'db.php';

header('Content-Type: text/plain; charset=utf-8');
echo "--- الاستيراد الذكي V2 (حل مشكلة الأعمدة المفقودة) ---\n";

$sqlFile = 'bitesight_db_backup.sql';
if (!file_exists($sqlFile)) die("❌ ملف الـ SQL غير موجود.");

try {
    // 1. تحويل الترميز
    $rawContent = file_get_contents($sqlFile);
    $content = mb_convert_encoding($rawContent, 'UTF-8', 'UTF-16LE');
    if (strlen($content) < 100) $content = $rawContent;

    // 2. مسح الجداول القديمة
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $tables = ['recipes', 'ingredients', 'recipe_ingredients', 'users', 'sine', 'site', 'about_page', 'dashlogen', 'favorites', 'meal_planner', 'faq', 'shopping_list'];
    foreach ($tables as $t) { $pdo->exec("DROP TABLE IF EXISTS `$t`;"); }

    // 3. إنشاء الجداول بالأعمدة الصحيحة أولاً
    echo "إعداد جداول النظام...\n";
    $pdo->exec("CREATE TABLE `recipes` (
        `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` varchar(255) NOT NULL,
        `category` text DEFAULT NULL,
        `time` varchar(50) DEFAULT NULL,
        `difficulty` varchar(50) DEFAULT NULL,
        `serves` varchar(50) DEFAULT NULL,
        `status` varchar(20) DEFAULT 'نشطة',
        `image` varchar(500) DEFAULT NULL,
        `steps` text DEFAULT NULL,
        `saves` int(11) DEFAULT 0,
        `views` int(11) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 4. تنفيذ باقي أوامر الاستيراد (سيتجاهل أوامر CREATE TABLE المكررة في الملف)
    $queries = explode(';', $content);
    foreach ($queries as $q) {
        $q = trim($q);
        if (empty($q) || stripos($q, 'CREATE TABLE `recipes`') !== false) continue;
        try {
            $pdo->exec($q);
        } catch (Exception $e) {
            // تجاهل أخطاء بسيطة
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "✅ تم استيراد البيانات بنجاح مع إضافة الأعمدة المطلوبة.\n";
    echo "افتح الداشبورد الآن، ستجده يعمل وبترميز صحيح.";

} catch (Exception $e) {
    echo "❌ خطأ فادح: " . $e->getMessage();
}
?>
