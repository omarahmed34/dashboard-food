<?php
require 'db.php';

// Fetch about content
try {
    $stmt = $pdo->query("SELECT * FROM about_page");
    $rawContent = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $content = [];
    foreach ($rawContent as $item) {
        $content[$item['lang']] = $item;
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

$lang = isset($_GET['lang']) && $_GET['lang'] === 'en' ? 'en' : 'ar';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';

// Helper to get content from flat structure
function c($field, $content, $lang) {
    return $content[$lang][$field] ?? ($content[$lang === 'ar' ? 'en' : 'ar'][$field] ?? '...');
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang === 'ar' ? 'من نحن | BiteSight' : 'About Us | BiteSight'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #8b5cf6;
            --secondary: #10b981;
            --bg: #0f172a;
            --glass: rgba(30, 41, 59, 0.7);
            --text: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            overflow-x: hidden;
            line-height: 1.6;
        }

        .aura {
            position: fixed;
            width: 60vw;
            height: 60vw;
            border-radius: 50%;
            filter: blur(100px);
            z-index: -1;
            opacity: 0.15;
            animation: pulse 15s infinite alternate ease-in-out;
        }
        .aura-1 { top: -15%; right: -10%; background: radial-gradient(circle, var(--primary) 0%, transparent 70%); }
        .aura-2 { bottom: -15%; left: -10%; background: radial-gradient(circle, var(--secondary) 0%, transparent 70%); }

        @keyframes pulse {
            0% { transform: translate(0,0) scale(1); opacity: 0.1; }
            100% { transform: translate(5%, 5%) scale(1.1); opacity: 0.2; }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 20px;
        }

        header {
            text-align: center;
            margin-bottom: 80px;
        }

        .badge {
            display: inline-block;
            padding: 6px 16px;
            background: rgba(139, 92, 246, 0.2);
            color: var(--primary);
            border-radius: 100px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 20px;
            border: 1px solid rgba(139, 92, 246, 0.3);
        }

        h1 {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 900;
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 24px;
        }

        .hero-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            margin-bottom: 100px;
        }

        @media (max-width: 768px) {
            .hero-section { grid-template-columns: 1fr; }
        }

        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            transition: transform 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            border-color: rgba(139, 92, 246, 0.4);
        }

        .hero-image {
            width: 100%;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .hero-image img {
            width: 100%;
            height: auto;
            display: block;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        @media (max-width: 640px) {
            .grid { grid-template-columns: 1fr; }
        }

        h4 {
            font-size: 24px;
            color: var(--primary);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        p {
            color: var(--text-muted);
            font-size: 18px;
        }

        .lang-switch {
            position: fixed;
            top: 20px;
            <?php echo $lang === 'ar' ? 'left' : 'right'; ?>: 20px;
            z-index: 100;
        }

        .btn-lang {
            padding: 10px 20px;
            background: var(--glass);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            border-radius: 12px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-lang:hover {
            background: var(--primary);
        }
    </style>
</head>
<body>

    <div class="aura aura-1"></div>
    <div class="aura aura-2"></div>

    <div class="lang-switch">
        <a href="?lang=<?php echo $lang === 'ar' ? 'en' : 'ar'; ?>" class="btn-lang">
            <?php echo $lang === 'ar' ? 'English' : 'العربية'; ?>
        </a>
    </div>

    <div class="container">
        <header>
            <div class="badge"><?php echo c('badge', $content, $lang); ?></div>
            <h1><?php echo c('title', $content, $lang); ?></h1>
        </header>

        <div class="hero-section">
            <div class="glass-card">
                <div class="grid">
                    <div class="info-block">
                        <h4>✨ <?php echo c('vision_title', $content, $lang); ?></h4>
                        <p><?php echo c('vision_text', $content, $lang); ?></p>
                    </div>
                </div>
                <div style="margin-top: 40px;" class="info-block">
                    <h4>🚀 <?php echo c('mission_title', $content, $lang); ?></h4>
                    <p><?php echo c('mission_text', $content, $lang); ?></p>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=800&q=80" alt="Cooking">
            </div>
        </div>
    </div>

</body>
</html>
