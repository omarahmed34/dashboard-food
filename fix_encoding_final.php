<?php
require_once 'db.php';

header('Content-Type: text/plain; charset=utf-8');
echo "--- إصلاح ترميز الجداول النهائي ---\n";

try {
    // 1. قائمة الجداول
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        echo "إصلاح الجدول: $table ... ";
        
        // تحويل الجدول بالكامل إلى الترميز العربي الصحيح
        $pdo->exec("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        
        echo "✅ تم.\n";
    }

    echo "\n--- الآن سنقوم بمسح البيانات القديمة وإعادة الاستيراد بشكل نظيف ---\n";
    
    // تشغيل الاستيراد مرة أخرى من داخل هذا الملف لضمان الترتيب
    require 'import_sql.php';
    
    echo "\nانتهت العملية. يرجى التحقق من لوحة التحكم الآن.";

} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>
