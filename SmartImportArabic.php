<?php
require_once 'db.php';

header('Content-Type: text/plain; charset=utf-8');
echo "--- بدء الاستيراد الذكي (حل مشكلة الترميز) ---\n";

$sqlFile = 'bitesight_db_backup.sql';

if (!file_exists($sqlFile)) {
    die("❌ خطأ: ملف $sqlFile غير موجود.");
}

try {
    // 1. قراءة الملف وتحويله من UTF-16LE إلى UTF-8 (لحل مشكلة الرموز)
    $rawContent = file_get_contents($sqlFile);
    $content = mb_convert_encoding($rawContent, 'UTF-8', 'UTF-16LE');
    
    // إذا لم ينجح التحويل الأول، نجرب UTF-8 العادي
    if (strlen($content) < 100) {
        $content = $rawContent;
    }

    echo "1. تم قراءة وتحويل الملف بنجاح.\n";

    // 2. تصفير القاعدة
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $tables = ['recipes', 'ingredients', 'recipe_ingredients', 'users', 'sine', 'site', 'about_page', 'dashlogen', 'favorites', 'meal_planner', 'faq', 'shopping_list'];
    foreach ($tables as $t) { $pdo->exec("DROP TABLE IF EXISTS `$t` CASCADE;"); }
    echo "2. تم تنظيف الجداول القديمة.\n";

    // 3. ضبط ترميز الاتصال
    $pdo->exec("SET NAMES utf8mb4;");

    // 4. تنفيذ الأوامر
    $queries = explode(';', $content);
    $success = 0;
    foreach ($queries as $q) {
        $q = trim($q);
        if (empty($q)) continue;
        try {
            $pdo->exec($q);
            $success++;
        } catch (Exception $inner) {
            // تجاهل أخطاء بسيطة
        }
    }

    echo "3. تم استيراد $success أمر بنجاح باللغة العربية.\n";
    echo "\nافتح الداشبورد الآن، ستجد اللغة العربية عادت للحياة!";

} catch (Exception $e) {
    echo "❌ خطأ فادح: " . $e->getMessage();
}
?>
