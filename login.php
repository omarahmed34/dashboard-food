<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$email    = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

// Validate input
if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'يرجى إدخال البريد الإلكتروني وكلمة المرور']);
    exit;
}

try {
    // Query the dashlogen table
    $stmt = $pdo->prepare("SELECT * FROM dashlogen WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'البريد الإلكتروني غير مسجل']);
        exit;
    }

    // Check password - supports both plain text and hashed passwords
    $passwordMatch = false;
    if (password_verify($password, $user['password'])) {
        // Hashed password (bcrypt)
        $passwordMatch = true;
    } elseif ($user['password'] === $password) {
        // Plain text (legacy - should be migrated)
        $passwordMatch = true;
    }

    if (!$passwordMatch) {
        echo json_encode(['status' => 'error', 'message' => 'كلمة المرور غير صحيحة']);
        exit;
    }

    // Success - return user info (never return password)
    echo json_encode([
        'status' => 'success',
        'message' => 'تم تسجيل الدخول بنجاح',
        'user' => [
            'id'    => $user['id'],
            'name'  => $user['name'] ?? 'Admin',
            'email' => $user['email'],
            'role'  => $user['role'] ?? 'admin'
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'خطأ في الخادم: ' . $e->getMessage()]);
}
?>
