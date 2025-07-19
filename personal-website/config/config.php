<?php
/**
 * Main Configuration File
 * PHP 8.0 Personal Website
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Site Configuration
define('SITE_NAME', 'موقعي الشخصي');
define('SITE_URL', 'http://localhost');
define('SITE_EMAIL', 'admin@example.com');
define('ADMIN_EMAIL', 'admin@example.com');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'personal_website');
define('DB_USER', 'root');
define('DB_PASS', '');

// Security Configuration
define('ENCRYPTION_KEY', 'your-secret-encryption-key-here');
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// File Upload Configuration
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Cache Configuration
define('CACHE_ENABLED', true);
define('CACHE_TIME', 3600); // 1 hour
define('CACHE_PATH', __DIR__ . '/../cache/');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Timezone
date_default_timezone_set('Asia/Riyadh');

// Include required files
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/../includes/functions.php';

// Initialize database connection
$db = new Database();

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// CSRF Token generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Get site setting from database
 */
function getSiteSetting($key, $default = '') {
    global $db;
    $result = $db->fetchOne("SELECT value FROM settings WHERE setting_key = ?", [$key]);
    return $result ? $result['value'] : $default;
}

/**
 * Update site setting in database
 */
function updateSiteSetting($key, $value) {
    global $db;
    $existing = $db->fetchOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
    
    if ($existing) {
        return $db->update('settings', ['value' => $value], 'setting_key = ?', [$key]);
    } else {
        return $db->insert('settings', ['setting_key' => $key, 'value' => $value]);
    }
}

/**
 * Check if user is logged in as admin
 */
function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Redirect to login if not admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /personal-website/admin/login.php');
        exit;
    }
}

/**
 * Generate CSRF token field
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

/**
 * Verify CSRF token
 */
function verifyCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
