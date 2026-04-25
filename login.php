<?php
// Prevent any accidental output before headers
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once 'db.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'طريقة الطلب غير مسموح بها']);
    exit;
}

// Get and decode JSON input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'بيانات الطلب غير صالحة']);
    exit;
}

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
    
    // Check if password column exists and has a value
    $storedPassword = $user['password'] ?? '';
    
    if (password_verify($password, $storedPassword)) {
        // Hashed password (bcrypt)
        $passwordMatch = true;
    } elseif ($storedPassword === $password) {
        // Plain text (legacy)
        $passwordMatch = true;
    }

    if (!$passwordMatch) {
        echo json_encode(['status' => 'error', 'message' => 'كلمة المرور غير صحيحة']);
        exit;
    }

    // Success - return user info
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

} catch (Throwable $e) {
    // Catch any error or exception
    echo json_encode([
        'status' => 'error', 
        'message' => 'خطأ في الخادم: ' . $e->getMessage()
    ]);
}

// Clear buffer and send output
ob_end_flush();
