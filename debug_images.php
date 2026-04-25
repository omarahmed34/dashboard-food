<?php
header('Content-Type: text/plain; charset=utf-8');
echo "--- فحص مجلد الصور ---\n";

$dir = 'uploads';

if (is_dir($dir)) {
    echo "✅ المجلد موجود.\n";
    echo "الصلاحيات: " . substr(sprintf('%o', fileperms($dir)), -4) . "\n";
    
    $files = array_diff(scandir($dir), array('.', '..'));
    echo "عدد الصور الموجودة: " . count($files) . "\n";
    foreach ($files as $file) {
        echo "- $file (" . filesize($dir.'/'.$file) . " bytes)\n";
    }
} else {
    echo "❌ المجلد غير موجود! جاري محاولة إنشائه...\n";
    if (mkdir($dir, 0777, true)) {
        echo "✅ تم إنشاء المجلد الآن. حاول الرفع مرة أخرى.";
    } else {
        echo "❌ فشل إنشاء المجلد. قد تحتاج لإنشائه يدوياً من File Manager باسم uploads";
    }
}
?>
