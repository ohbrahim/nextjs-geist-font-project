<?php
/**
 * Security Functions
 * PHP 8.0 Personal Website
 */

class Security {
    
    /**
     * Sanitize input data
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate secure random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Encrypt data
     */
    public static function encrypt($data) {
        $key = ENCRYPTION_KEY;
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt data
     */
    public static function decrypt($data) {
        $key = ENCRYPTION_KEY;
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
    
    /**
     * Check for SQL injection patterns
     */
    public static function detectSqlInjection($input) {
        $patterns = [
            '/(\bunion\b.*\bselect\b)/i',
            '/(\bselect\b.*\bfrom\b)/i',
            '/(\binsert\b.*\binto\b)/i',
            '/(\bdelete\b.*\bfrom\b)/i',
            '/(\bdrop\b.*\btable\b)/i',
            '/(\bupdate\b.*\bset\b)/i',
            '/(\'|\"|;|--|\#)/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check for XSS patterns
     */
    public static function detectXss($input) {
        $patterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/mi'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Rate limiting for login attempts
     */
    public static function checkLoginAttempts($ip) {
        global $db;
        
        // Clean old attempts
        $db->query("DELETE FROM login_attempts WHERE attempt_time < ?", [time() - LOGIN_LOCKOUT_TIME]);
        
        // Count recent attempts
        $attempts = $db->fetchOne("SELECT COUNT(*) as count FROM login_attempts WHERE ip_address = ? AND attempt_time > ?", 
            [$ip, time() - LOGIN_LOCKOUT_TIME]);
        
        return $attempts['count'] < MAX_LOGIN_ATTEMPTS;
    }
    
    /**
     * Log failed login attempt
     */
    public static function logFailedLogin($ip, $username = '') {
        global $db;
        
        $db->insert('login_attempts', [
            'ip_address' => $ip,
            'username' => $username,
            'attempt_time' => time()
        ]);
    }
    
    /**
     * Clear login attempts for IP
     */
    public static function clearLoginAttempts($ip) {
        global $db;
        $db->delete('login_attempts', 'ip_address = ?', [$ip]);
    }
    
    /**
     * Get client IP address
     */
    public static function getClientIp() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file) {
        $errors = [];
        
        // Check if file was uploaded
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'خطأ في رفع الملف';
            return $errors;
        }
        
        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'حجم الملف كبير جداً';
        }
        
        // Check file type
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension'] ?? '');
        
        if (!in_array($extension, ALLOWED_IMAGE_TYPES)) {
            $errors[] = 'نوع الملف غير مسموح';
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'image/gif',
            'image/webp'
        ];
        
        if (!in_array($mimeType, $allowedMimes)) {
            $errors[] = 'نوع الملف غير صحيح';
        }
        
        return $errors;
    }
}
?>
