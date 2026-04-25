<?php
/**
 * setup_database_tables.php
 * Run this file in your browser to create all missing tables.
 * Example: http://your-site.com/setup_database_tables.php
 */
require_once 'db.php';

$tables = [
    "recipes" => "CREATE TABLE IF NOT EXISTS `recipes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `category` text DEFAULT NULL,
        `time` varchar(50) DEFAULT NULL,
        `difficulty` varchar(50) DEFAULT NULL,
        `serves` varchar(50) DEFAULT NULL,
        `image` varchar(500) DEFAULT NULL,
        `steps` text DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    "ingredients" => "CREATE TABLE IF NOT EXISTS `ingredients` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `emoji` varchar(50) DEFAULT NULL,
        `category` varchar(100) DEFAULT NULL,
        `used_in` int(11) DEFAULT 1,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    "recipe_ingredients" => "CREATE TABLE IF NOT EXISTS `recipe_ingredients` (
        `recipe_id` int(11) NOT NULL,
        `ingredient_id` int(11) NOT NULL,
        PRIMARY KEY (`recipe_id`,`ingredient_id`),
        KEY `ingredient_id` (`ingredient_id`),
        CONSTRAINT `recipe_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
        CONSTRAINT `recipe_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    "users" => "CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `email` varchar(150) NOT NULL,
        `avatar` varchar(255) DEFAULT NULL,
        `join_date` date DEFAULT NULL,
        `saves` int(11) DEFAULT 0,
        `status` varchar(20) DEFAULT 'جديد',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    "sine" => "CREATE TABLE IF NOT EXISTS `sine` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `full_name` varchar(100) NOT NULL,
        `email` varchar(150) NOT NULL,
        `password` varchar(255) NOT NULL,
        `terms_accepted` tinyint(1) NOT NULL DEFAULT 1,
        `provider` enum('local','google','facebook') DEFAULT 'local',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    "site" => "CREATE TABLE IF NOT EXISTS `site` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `content_key` varchar(100) NOT NULL,
        `content_value` text NOT NULL,
        `lang` varchar(10) DEFAULT 'ar',
        PRIMARY KEY (`id`),
        UNIQUE KEY `content_key` (`content_key`,`lang`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    "about_page" => "CREATE TABLE IF NOT EXISTS about_page (
        id INT AUTO_INCREMENT PRIMARY KEY,
        content_key VARCHAR(100) NOT NULL,
        content_value TEXT,
        lang ENUM('ar', 'en') DEFAULT 'ar',
        UNIQUE KEY (content_key, lang)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    "dashlogen" => "CREATE TABLE IF NOT EXISTS `dashlogen` (
        `id`         INT AUTO_INCREMENT PRIMARY KEY,
        `name`       VARCHAR(100)  NOT NULL DEFAULT 'Admin',
        `email`      VARCHAR(150)  NOT NULL UNIQUE,
        `password`   VARCHAR(255)  NOT NULL,
        `role`       VARCHAR(50)   NOT NULL DEFAULT 'admin',
        `created_at` TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "favorites" => "CREATE TABLE IF NOT EXISTS `favorites` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `recipe_id` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "meal_planner" => "CREATE TABLE IF NOT EXISTS meal_planner (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        recipe_id INT NOT NULL,
        day_of_week ENUM('Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
        meal_type ENUM('Breakfast', 'Lunch', 'Dinner', 'Snack') DEFAULT 'Lunch',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "faq" => "CREATE TABLE IF NOT EXISTS faq (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question_ar TEXT NOT NULL,
        answer_ar TEXT NOT NULL,
        question_en TEXT NOT NULL,
        answer_en TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "shopping_list" => "CREATE TABLE IF NOT EXISTS shopping_list (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        ingredient_name VARCHAR(255) NOT NULL,
        recipe_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    "orders" => "CREATE TABLE IF NOT EXISTS `orders` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `recipe_id` int(11) DEFAULT NULL,
        `full_name` varchar(255) NOT NULL,
        `emall` varchar(150) DEFAULT NULL,
        `phone` varchar(50) NOT NULL,
        `address` text NOT NULL,
        `delivery_date` date DEFAULT NULL,
        `delivery_time` time DEFAULT NULL,
        `location_link` text DEFAULT NULL,
        `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
        `status` varchar(50) DEFAULT 'pending',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

header('Content-Type: text/plain; charset=utf-8');
echo "Starting Database Setup...\n\n";

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ Table `$name` ready.\n";
    } catch (PDOException $e) {
        echo "❌ Error creating table `$name`: " . $e->getMessage() . "\n";
    }
}

echo "\nSetup complete! You can now use the website.";
?>
