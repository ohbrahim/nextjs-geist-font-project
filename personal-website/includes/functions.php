<?php
/**
 * Common Functions
 * PHP 8.0 Personal Website
 */

/**
 * Redirect function
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Display success message
 */
function showSuccess($message) {
    $_SESSION['success_message'] = $message;
}

/**
 * Display error message
 */
function showError($message) {
    $_SESSION['error_message'] = $message;
}

/**
 * Get and clear success message
 */
function getSuccessMessage() {
    if (isset($_SESSION['success_message'])) {
        $message = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
        return $message;
    }
    return null;
}

/**
 * Get and clear error message
 */
function getErrorMessage() {
    if (isset($_SESSION['error_message'])) {
        $message = $_SESSION['error_message'];
        unset($_SESSION['error_message']);
        return $message;
    }
    return null;
}

/**
 * Format date in Arabic
 */
function formatArabicDate($date, $format = 'Y-m-d H:i') {
    $arabicMonths = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
        5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
        9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
    ];
    
    $timestamp = is_string($date) ? strtotime($date) : $date;
    $formatted = date($format, $timestamp);
    
    // Replace English month names with Arabic
    foreach ($arabicMonths as $num => $arabic) {
        $english = date('F', mktime(0, 0, 0, $num, 1));
        $formatted = str_replace($english, $arabic, $formatted);
    }
    
    return $formatted;
}

/**
 * Truncate text
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Generate slug from Arabic text
 */
function generateSlug($text) {
    // Remove HTML tags
    $text = strip_tags($text);
    
    // Convert to lowercase
    $text = mb_strtolower($text, 'UTF-8');
    
    // Replace Arabic characters with English equivalents
    $arabic = ['ا', 'ب', 'ت', 'ث', 'ج', 'ح', 'خ', 'د', 'ذ', 'ر', 'ز', 'س', 'ش', 'ص', 'ض', 'ط', 'ظ', 'ع', 'غ', 'ف', 'ق', 'ك', 'ل', 'م', 'ن', 'ه', 'و', 'ي'];
    $english = ['a', 'b', 't', 'th', 'j', 'h', 'kh', 'd', 'th', 'r', 'z', 's', 'sh', 's', 'd', 't', 'th', 'a', 'gh', 'f', 'q', 'k', 'l', 'm', 'n', 'h', 'w', 'y'];
    
    $text = str_replace($arabic, $english, $text);
    
    // Replace spaces and special characters with hyphens
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    
    // Remove multiple hyphens
    $text = preg_replace('/-+/', '-', $text);
    
    // Trim hyphens from start and end
    return trim($text, '-');
}

/**
 * Upload file
 */
function uploadFile($file, $directory = 'uploads') {
    $uploadDir = __DIR__ . "/../$directory/";
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file
    $errors = Security::validateFileUpload($file);
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Generate unique filename
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'path' => "$directory/$filename"];
    }
    
    return ['success' => false, 'errors' => ['فشل في رفع الملف']];
}

/**
 * Delete file
 */
function deleteFile($filepath) {
    $fullPath = __DIR__ . "/../$filepath";
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Resize image
 */
function resizeImage($source, $destination, $maxWidth = 800, $maxHeight = 600, $quality = 85) {
    $imageInfo = getimagesize($source);
    if (!$imageInfo) {
        return false;
    }
    
    $originalWidth = $imageInfo[0];
    $originalHeight = $imageInfo[1];
    $mimeType = $imageInfo['mime'];
    
    // Calculate new dimensions
    $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
    $newWidth = intval($originalWidth * $ratio);
    $newHeight = intval($originalHeight * $ratio);
    
    // Create image resource
    switch ($mimeType) {
        case 'image/jpeg':
            $sourceImage = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $sourceImage = imagecreatefromgif($source);
            break;
        case 'image/webp':
            $sourceImage = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }
    
    // Create new image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG and GIF
    if ($mimeType == 'image/png' || $mimeType == 'image/gif') {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // Resize image
    imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
    
    // Save image
    $result = false;
    switch ($mimeType) {
        case 'image/jpeg':
            $result = imagejpeg($newImage, $destination, $quality);
            break;
        case 'image/png':
            $result = imagepng($newImage, $destination, 9);
            break;
        case 'image/gif':
            $result = imagegif($newImage, $destination);
            break;
        case 'image/webp':
            $result = imagewebp($newImage, $destination, $quality);
            break;
    }
    
    // Clean up
    imagedestroy($sourceImage);
    imagedestroy($newImage);
    
    return $result;
}

/**
 * Get page content by slug
 */
function getPageBySlug($slug) {
    global $db;
    return $db->fetchOne("SELECT * FROM pages WHERE slug = ? AND is_active = 1", [$slug]);
}

/**
 * Get menu pages
 */
function getMenuPages() {
    global $db;
    return $db->fetchAll("SELECT * FROM pages WHERE show_in_menu = 1 AND is_active = 1 ORDER BY menu_order ASC");
}

/**
 * Get active services
 */
function getActiveServices() {
    global $db;
    return $db->fetchAll("SELECT * FROM services WHERE is_active = 1 ORDER BY display_order ASC");
}

/**
 * Get active portfolio items
 */
function getActivePortfolio($limit = null) {
    global $db;
    $sql = "SELECT * FROM portfolio WHERE is_active = 1 ORDER BY display_order ASC";
    if ($limit) {
        $sql .= " LIMIT $limit";
    }
    return $db->fetchAll($sql);
}

/**
 * Get active banners
 */
function getActiveBanners($type = null) {
    global $db;
    $sql = "SELECT * FROM banners WHERE is_active = 1";
    $params = [];
    
    if ($type) {
        $sql .= " AND banner_type = ?";
        $params[] = $type;
    }
    
    $sql .= " AND (start_date IS NULL OR start_date <= CURDATE())";
    $sql .= " AND (end_date IS NULL OR end_date >= CURDATE())";
    $sql .= " ORDER BY display_order ASC";
    
    return $db->fetchAll($sql, $params);
}

/**
 * Get active poll
 */
function getActivePoll() {
    global $db;
    $poll = $db->fetchOne("SELECT * FROM polls WHERE is_active = 1 AND (start_date IS NULL OR start_date <= CURDATE()) AND (end_date IS NULL OR end_date >= CURDATE()) ORDER BY created_at DESC LIMIT 1");
    
    if ($poll) {
        $poll['options'] = $db->fetchAll("SELECT * FROM poll_options WHERE poll_id = ? ORDER BY display_order ASC", [$poll['id']]);
    }
    
    return $poll;
}

/**
 * Track visitor
 */
function trackVisitor() {
    global $db;
    
    $ip = Security::getClientIp();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $page = $_SERVER['REQUEST_URI'] ?? '';
    $sessionId = session_id();
    
    // Insert visitor record
    $db->insert('visitors', [
        'ip_address' => $ip,
        'user_agent' => $userAgent,
        'page_visited' => $page,
        'session_id' => $sessionId
    ]);
    
    // Update online users
    $db->query("INSERT INTO online_users (ip_address, session_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE last_activity = CURRENT_TIMESTAMP", [$ip, $sessionId]);
    
    // Clean old online users (older than 5 minutes)
    $db->query("DELETE FROM online_users WHERE last_activity < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
}

/**
 * Get visitor statistics
 */
function getVisitorStats() {
    global $db;
    
    $stats = [];
    
    // Total visitors
    $result = $db->fetchOne("SELECT COUNT(DISTINCT ip_address) as total FROM visitors");
    $stats['total_visitors'] = $result['total'] ?? 0;
    
    // Online users
    $result = $db->fetchOne("SELECT COUNT(*) as online FROM online_users");
    $stats['online_users'] = $result['online'] ?? 0;
    
    // Today's visitors
    $result = $db->fetchOne("SELECT COUNT(DISTINCT ip_address) as today FROM visitors WHERE DATE(visit_time) = CURDATE()");
    $stats['today_visitors'] = $result['today'] ?? 0;
    
    // This month's visitors
    $result = $db->fetchOne("SELECT COUNT(DISTINCT ip_address) as month FROM visitors WHERE MONTH(visit_time) = MONTH(CURDATE()) AND YEAR(visit_time) = YEAR(CURDATE())");
    $stats['month_visitors'] = $result['month'] ?? 0;
    
    return $stats;
}

/**
 * Send email
 */
function sendEmail($to, $subject, $message, $from = null) {
    $from = $from ?: getSiteSetting('contact_email', 'noreply@example.com');
    $siteName = getSiteSetting('site_name', 'الموقع الشخصي');
    
    $headers = [
        'From' => "$siteName <$from>",
        'Reply-To' => $from,
        'Content-Type' => 'text/html; charset=UTF-8',
        'X-Mailer' => 'PHP/' . phpversion()
    ];
    
    $headerString = '';
    foreach ($headers as $key => $value) {
        $headerString .= "$key: $value\r\n";
    }
    
    return mail($to, $subject, $message, $headerString);
}

/**
 * Generate pagination
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav class="pagination">';
    $html .= '<ul class="pagination-list">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<li><a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '">السابق</a></li>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $html .= '<li><a href="' . $baseUrl . '?page=1">1</a></li>';
        if ($start > 2) {
            $html .= '<li><span>...</span></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $html .= '<li><span class="current">' . $i . '</span></li>';
        } else {
            $html .= '<li><a href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<li><span>...</span></li>';
        }
        $html .= '<li><a href="' . $baseUrl . '?page=' . $totalPages . '">' . $totalPages . '</a></li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<li><a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '">التالي</a></li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}
?>
