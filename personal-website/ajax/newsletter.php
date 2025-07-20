<?php
/**
 * Newsletter Subscription Handler - Personal Website
 * PHP 8.0 Personal Website
 */

require_once '../config/config.php';

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Verify CSRF token
if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Get form data
$name = Security::sanitize($_POST['name'] ?? '');
$email = Security::sanitize($_POST['email'] ?? '');

// Validate inputs
$errors = [];

if (empty($name)) {
    $errors[] = 'الاسم مطلوب';
}

if (empty($email) || !Security::validateEmail($email)) {
    $errors[] = 'البريد الإلكتروني غير صالح';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Check if email already exists
$existing = $db->fetchOne("SELECT id FROM newsletter WHERE email = ?", [$email]);

if ($existing) {
    echo json_encode(['success' => false, 'message' => 'هذا البريد الإلكتروني مسجل مسبقاً']);
    exit;
}

// Generate verification token
$verificationToken = Security::generateToken(32);

// Insert subscriber
$result = $db->insert('newsletter', [
    'name' => $name,
    'email' => $email,
    'verification_token' => $verificationToken,
    'is_verified' => 0
]);

if ($result) {
    // Send verification email
    $subject = 'تأكيد الاشتراك في النشرة البريدية';
    $message = "
        <h2>مرحباً $name</h2>
        <p>شكراً لاشتراكك في نشرتنا البريدية!</p>
        <p>للتأكيد على اشتراكك، يرجى النقر على الرابط التالي:</p>
        <p><a href='https://yourwebsite.com/verify-newsletter.php?token=$verificationToken'>تأكيد الاشتراك</a></p>
    ";
    
    sendEmail($email, $subject, $message);
    
    echo json_encode(['success' => true, 'message' => 'تم الاشتراك بنجاح! يرجى التحقق من بريدك الإلكتروني']);
} else {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء التسجيل']);
}
?>
