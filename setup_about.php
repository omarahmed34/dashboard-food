<?php
require_once 'db.php';

try {
    // 1. Create the about_page table (Flat Structure)
    $createTable = "CREATE TABLE IF NOT EXISTS about_page (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lang ENUM('ar', 'en') NOT NULL UNIQUE,
        badge VARCHAR(255),
        title VARCHAR(255),
        vision_title VARCHAR(255),
        vision_text TEXT,
        mission_title VARCHAR(255),
        mission_text TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($createTable);
    echo "Table 'about_page' initialized.<br>";

    // 2. Insert default data if empty
    $check = $pdo->query("SELECT COUNT(*) FROM about_page")->fetchColumn();
    if ($check == 0) {
        $defaults = [
            [
                'lang' => 'ar',
                'badge' => 'قصتنا',
                'title' => 'نحن نؤمن بأن الطعام هو لغة الحب العالمية',
                'vision_title' => 'رؤيتنا',
                'vision_text' => 'أن نكون المنصة الأولى في العالم لاكتشاف وابتكار الوصفات الشهية بكل سهولة.',
                'mission_title' => 'مهمتنا',
                'mission_text' => 'توفير أدوات وتقنيات ذكية تساعد الطهاة والمبتدئين على تحويل مطابخهم إلى مطاعم فاخرة.'
            ],
            [
                'lang' => 'en',
                'badge' => 'Our Story',
                'title' => 'We believe food is the universal language of love',
                'vision_title' => 'Our Vision',
                'vision_text' => 'To be the world\'s leading platform for discovering and creating delicious recipes with ease.',
                'mission_title' => 'Our Mission',
                'mission_text' => 'Providing smart tools and techniques that help chefs and beginners turn their kitchens into gourmet restaurants.'
            ]
        ];

        $stmt = $pdo->prepare("INSERT INTO about_page (lang, badge, title, vision_title, vision_text, mission_title, mission_text) 
                               VALUES (:lang, :badge, :title, :vision_title, :vision_text, :mission_title, :mission_text)");
        foreach ($defaults as $row) {
            $stmt->execute($row);
        }
        echo "Default about content inserted.<br>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
