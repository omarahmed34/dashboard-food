<?php
require 'db.php';

try {
    // 1. Create the about_page table
    $createTable = "CREATE TABLE IF NOT EXISTS about_page (
        id INT AUTO_INCREMENT PRIMARY KEY,
        content_key VARCHAR(100) NOT NULL,
        content_value TEXT,
        lang ENUM('ar', 'en') DEFAULT 'ar',
        UNIQUE KEY (content_key, lang)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($createTable);
    echo "Table 'about_page' created successfully or already exists.<br>";

    // 2. Insert default data if empty
    $check = $pdo->query("SELECT COUNT(*) FROM about_page")->fetchColumn();
    if ($check == 0) {
        $defaults = [
            // AR
            ['about.badge', 'قصتنا', 'ar'],
            ['about.h3', 'نحن نؤمن بأن الطعام هو لغة الحب العالمية', 'ar'],
            ['about.v.h4', 'رؤيتنا', 'ar'],
            ['about.v.p', 'أن نكون المنصة الأولى في العالم لاكتشاف وابتكار الوصفات الشهية بكل سهولة.', 'ar'],
            ['about.m.h4', 'مهمتنا', 'ar'],
            ['about.m.p', 'توفير أدوات وتقنيات ذكية تساعد الطهاة والمبتدئين على تحويل مطابخهم إلى مطاعم فاخرة.', 'ar'],
            // EN
            ['about.badge', 'Our Story', 'en'],
            ['about.h3', 'We believe food is the universal language of love', 'en'],
            ['about.v.h4', 'Our Vision', 'en'],
            ['about.v.p', 'To be the world\'s leading platform for discovering and creating delicious recipes with ease.', 'en'],
            ['about.m.h4', 'Our Mission', 'en'],
            ['about.m.p', 'Providing smart tools and techniques that help chefs and beginners turn their kitchens into gourmet restaurants.', 'en'],
        ];

        $stmt = $pdo->prepare("INSERT INTO about_page (content_key, content_value, lang) VALUES (?, ?, ?)");
        foreach ($defaults as $row) {
            $stmt->execute($row);
        }
        echo "Default data inserted into 'about_page'.<br>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
