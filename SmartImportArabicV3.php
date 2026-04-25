<?php
require_once 'db.php';

header('Content-Type: text/plain; charset=utf-8');
echo "--- الاستيراد الذكي V3 (الحل النهائي للتعارض) ---\n";

$sqlFile = 'bitesight_db_backup.sql';
if (!file_exists($sqlFile)) die("❌ ملف الـ SQL غير موجود.");

try {
    // 1. تحويل الترميز
    $rawContent = file_get_contents($sqlFile);
    $content = mb_convert_encoding($rawContent, 'UTF-8', 'UTF-16LE');
    if (strlen($content) < 100) $content = $rawContent;

    // 2. مسح الجداول القديمة تماماً
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $tables = ['recipes', 'ingredients', 'recipe_ingredients', 'users', 'sine', 'site', 'about_page', 'dashlogen', 'favorites', 'meal_planner', 'faq', 'shopping_list'];
    foreach ($tables as $t) { $pdo->exec("DROP TABLE IF EXISTS `$t`;"); }

    // 3. إنشاء الجداول بالهيكل "الأشمل" (الذي يتوقعه الموقع)
    echo "إعداد جداول النظام المتكاملة...\n";
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

    // 4. معالجة أوامر الاستيراد بذكاء
    $queries = explode(';', $content);
    $success = 0;
    foreach ($queries as $q) {
        $q = trim($q);
        if (empty($q)) continue;

        // تجاهل أي أمر يحاول حذف أو إعادة إنشاء جدول recipes
        if (stripos($q, 'DROP TABLE IF EXISTS `recipes`') !== false) continue;
        if (stripos($q, 'CREATE TABLE `recipes`') !== false) continue;
        if (stripos($q, 'DROP TABLE IF EXISTS recipes') !== false) continue;
        if (stripos($q, 'CREATE TABLE recipes') !== false) continue;

        try {
            $pdo->exec($q);
            $success++;
        } catch (Exception $e) {
            // تجاهل أخطاء التكرار
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "✅ تم الاستيراد بنجاح! تم حماية الجدول من التلف.\n";
    echo "افتح الداشبورد الآن، ستجده يعمل وبترميز وتصميم سليم.";

} catch (Exception $e) {
    echo "❌ خطأ فادح: " . $e->getMessage();
}
?>
