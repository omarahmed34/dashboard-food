<?php
// ===== DIAGNOSTIC PAGE =====
// Upload this file and visit: yourdomain.com/check_db.php
header('Content-Type: text/html; charset=utf-8');

$host_name   = $_SERVER['HTTP_HOST'] ?? 'unknown';
$server_addr = $_SERVER['SERVER_ADDR'] ?? 'unknown';
$current_dir = __DIR__;

$is_local = (
    strpos($current_dir, 'xampp') !== false ||
    strpos($host_name, 'localhost') !== false ||
    strpos($host_name, '127.0.0.1') !== false ||
    $server_addr === '127.0.0.1' ||
    $server_addr === '::1'
);

echo "<style>body{font-family:monospace;padding:20px;background:#0f172a;color:#e2e8f0;}
.ok{color:#10b981;}.err{color:#ef4444;}.info{color:#a78bfa;}
table{border-collapse:collapse;width:100%;margin:20px 0;}
tr:nth-child(even){background:rgba(255,255,255,0.05);}
th,td{padding:10px 15px;text-align:right;border:1px solid #334155;}
th{background:#1e293b;color:#a78bfa;}
</style>";
echo "<h2 style='color:#a78bfa;'>🔍 تشخيص الاتصال بقاعدة البيانات - BiteSight</h2>";

echo "<p class='info'>🌍 السيرفر: <b>$host_name</b> | المسار: $current_dir</p>";
echo "<p class='info'>🔧 وضع الاتصال: <b>" . ($is_local ? "محلي (XAMPP)" : "أونلاين (Remote)") . "</b></p>";

// Now try to connect
if ($is_local) {
    $host = 'localhost'; $dbname = 'bitesight_db'; $user = 'root'; $pass = '';
} else {
    // Try to read the ACTUAL database name from InfinityFree
    $host   = 'sql312.infinityfree.com';
    $dbname = 'if0_41641864_if0_41641864_if0_41641864_if0_12345678_dbname';
    $user   = 'if0_41641864';
    $pass   = 'O6fTYjh6f9';
}

echo "<p class='info'>📦 قاعدة البيانات المستخدمة: <b>$dbname</b> على <b>$host</b></p>";
echo "<p class='info'>👤 المستخدم: <b>$user</b></p>";
echo "<hr style='border-color:#334155; margin:20px 0;'>";

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "<p class='ok'>✅ الاتصال بقاعدة البيانات نجح!</p>";

    // Show all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p class='ok'>📋 الجداول الموجودة: " . implode(', ', $tables) . "</p>";

    // Check orders table
    if (in_array('orders', $tables)) {
        $rows = $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();
        echo "<p class='ok'>📦 جدول الطلبات (orders): <b>" . count($rows) . " طلب</b></p>";

        if (count($rows) > 0) {
            echo "<table><tr>";
            foreach (array_keys($rows[0]) as $col) echo "<th>$col</th>";
            echo "</tr>";
            foreach ($rows as $row) {
                echo "<tr>";
                foreach ($row as $val) echo "<td>" . htmlspecialchars($val ?? '-') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color:#f59e0b;'>⚠️ الجدول موجود لكن لا توجد بيانات بعد.</p>";
        }
    } else {
        echo "<p class='err'>❌ جدول orders غير موجود! اضغط الرابط لإنشائه: <a href='setup_database_tables.php' style='color:#a78bfa;'>setup_database_tables.php</a></p>";
    }

    // Check contact table
    if (in_array('contact', $tables)) {
        $contacts = $pdo->query("SELECT * FROM contact ORDER BY id DESC")->fetchAll();
        echo "<p class='ok'>📧 جدول الرسائل (contact): <b>" . count($contacts) . " رسالة</b></p>";
    }

} catch (PDOException $e) {
    echo "<p class='err'>❌ فشل الاتصال: " . $e->getMessage() . "</p>";
    echo "<p style='color:#f59e0b;'>💡 السبب الأكثر احتمالاً: اسم قاعدة البيانات أو كلمة السر غير صحيحة.</p>";
    echo "<p style='color:#f59e0b;'>ادخل على حسابك في InfinityFree → MySQL Databases → انسخ اسم وكلمة سر قاعدة البيانات وأرسلها لي.</p>";
}
?>
