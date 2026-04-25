<?php
require_once 'db.php';

header('Content-Type: text/plain; charset=utf-8');
echo "--- بدء عملية الاستيراد التلقائي ---\n";

$sqlFile = 'bitesight_db_backup.sql';

if (!file_exists($sqlFile)) {
    die("❌ خطأ: ملف $sqlFile غير موجود على الخادم. يرجى رفعه أولاً.");
}

try {
    // قراءة الملف (معالجة ترميز UTF-16 إذا وجد)
    $content = file_get_contents($sqlFile);
    if (strpos($content, "\0") !== false) {
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
    }

    // تقسيم الملف إلى أوامر منفصلة
    $queries = explode(';', $content);
    $success = 0;
    $errors = 0;

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;

        try {
            $pdo->exec($query);
            $success++;
        } catch (Exception $e) {
            $errors++;
            // لا نتوقف عند الخطأ الصغير ونكمل البقية
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "✅ اكتملت العملية!\n";
    echo "تم تنفيذ $success أمر بنجاح.\n";
    if ($errors > 0) echo "هناك $errors أوامر واجهت مشاكل (قد تكون الجداول موجودة مسبقاً).\n";
    
    echo "\nيرجى تجربة الموقع الآن.";

} catch (Exception $e) {
    echo "❌ خطأ فادح: " . $e->getMessage();
}
?>
