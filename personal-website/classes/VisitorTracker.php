<?php
/**
 * Visitor Tracking Class
 * PHP 8.0 Personal Website
 */

class VisitorTracker {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Track visitor
     */
    public function track() {
        $ip = Security::getClientIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $page = $_SERVER['REQUEST_URI'] ?? '';
        $sessionId = session_id();
        
        // Insert visitor record
        $this->db->insert('visitors', [
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'page_visited' => $page,
            'session_id' => $sessionId
        ]);
        
        // Update online users
        $this->updateOnlineUsers($ip, $sessionId);
        
        // Clean old records
        $this->cleanOldRecords();
    }
    
    /**
     * Update online users
     */
    private function updateOnlineUsers($ip, $sessionId) {
        $existing = $this->db->fetchOne("SELECT id FROM online_users WHERE ip_address = ?", [$ip]);
        
        if ($existing) {
            $this->db->update('online_users', 
                ['last_activity' => date('Y-m-d H:i:s'), 'session_id' => $sessionId], 
                'ip_address = ?', 
                [$ip]
            );
        } else {
            $this->db->insert('online_users', [
                'ip_address' => $ip,
                'session_id' => $sessionId,
                'last_activity' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    /**
     * Clean old records
     */
    private function cleanOldRecords() {
        // Remove online users inactive for more than 5 minutes
        $this->db->query("DELETE FROM online_users WHERE last_activity < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
        
        // Remove old visitor records (older than 1 year)
        $this->db->query("DELETE FROM visitors WHERE visit_time < DATE_SUB(NOW(), INTERVAL 1 YEAR)");
    }
    
    /**
     * Get total visitors count
     */
    public function getTotalVisitors() {
        $result = $this->db->fetchOne("SELECT COUNT(DISTINCT ip_address) as total FROM visitors");
        return $result['total'] ?? 0;
    }
    
    /**
     * Get online users count
     */
    public function getOnlineUsers() {
        $result = $this->db->fetchOne("SELECT COUNT(*) as online FROM online_users WHERE last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
        return $result['online'] ?? 0;
    }
    
    /**
     * Get today's visitors
     */
    public function getTodayVisitors() {
        $result = $this->db->fetchOne("SELECT COUNT(DISTINCT ip_address) as today FROM visitors WHERE DATE(visit_time) = CURDATE()");
        return $result['today'] ?? 0;
    }
    
    /**
     * Get this week's visitors
     */
    public function getWeekVisitors() {
        $result = $this->db->fetchOne("SELECT COUNT(DISTINCT ip_address) as week FROM visitors WHERE YEARWEEK(visit_time) = YEARWEEK(CURDATE())");
        return $result['week'] ?? 0;
    }
    
    /**
     * Get this month's visitors
     */
    public function getMonthVisitors() {
        $result = $this->db->fetchOne("SELECT COUNT(DISTINCT ip_address) as month FROM visitors WHERE MONTH(visit_time) = MONTH(CURDATE()) AND YEAR(visit_time) = YEAR(CURDATE())");
        return $result['month'] ?? 0;
    }
    
    /**
     * Get visitor statistics by date range
     */
    public function getVisitorsByDateRange($startDate, $endDate) {
        return $this->db->fetchAll("
            SELECT DATE(visit_time) as date, COUNT(DISTINCT ip_address) as visitors 
            FROM visitors 
            WHERE DATE(visit_time) BETWEEN ? AND ? 
            GROUP BY DATE(visit_time) 
            ORDER BY date ASC
        ", [$startDate, $endDate]);
    }
    
    /**
     * Get most visited pages
     */
    public function getMostVisitedPages($limit = 10) {
        return $this->db->fetchAll("
            SELECT page_visited, COUNT(*) as visits 
            FROM visitors 
            WHERE page_visited IS NOT NULL AND page_visited != '' 
            GROUP BY page_visited 
            ORDER BY visits DESC 
            LIMIT ?
        ", [$limit]);
    }
    
    /**
     * Get visitor countries (if GeoIP is available)
     */
    public function getVisitorCountries($limit = 10) {
        return $this->db->fetchAll("
            SELECT country, COUNT(DISTINCT ip_address) as visitors 
            FROM visitors 
            WHERE country IS NOT NULL AND country != '' 
            GROUP BY country 
            ORDER BY visitors DESC 
            LIMIT ?
        ", [$limit]);
    }
    
    /**
     * Get hourly visitor distribution
     */
    public function getHourlyDistribution() {
        return $this->db->fetchAll("
            SELECT HOUR(visit_time) as hour, COUNT(*) as visits 
            FROM visitors 
            WHERE DATE(visit_time) = CURDATE() 
            GROUP BY HOUR(visit_time) 
            ORDER BY hour ASC
        ");
    }
    
    /**
     * Get browser statistics
     */
    public function getBrowserStats() {
        $visitors = $this->db->fetchAll("SELECT DISTINCT user_agent FROM visitors WHERE user_agent IS NOT NULL");
        $browsers = [];
        
        foreach ($visitors as $visitor) {
            $browser = $this->detectBrowser($visitor['user_agent']);
            if (isset($browsers[$browser])) {
                $browsers[$browser]++;
            } else {
                $browsers[$browser] = 1;
            }
        }
        
        arsort($browsers);
        return array_slice($browsers, 0, 10, true);
    }
    
    /**
     * Detect browser from user agent
     */
    private function detectBrowser($userAgent) {
        $browsers = [
            'Chrome' => '/Chrome/i',
            'Firefox' => '/Firefox/i',
            'Safari' => '/Safari/i',
            'Edge' => '/Edge/i',
            'Opera' => '/Opera/i',
            'Internet Explorer' => '/MSIE/i'
        ];
        
        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $browser;
            }
        }
        
        return 'Unknown';
    }
    
    /**
     * Check if visitor is unique today
     */
    public function isUniqueToday($ip) {
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM visitors WHERE ip_address = ? AND DATE(visit_time) = CURDATE()", [$ip]);
        return $result['count'] == 0;
    }
    
    /**
     * Get visitor details
     */
    public function getVisitorDetails($limit = 50, $offset = 0) {
        return $this->db->fetchAll("
            SELECT ip_address, user_agent, page_visited, visit_time, country, city 
            FROM visitors 
            ORDER BY visit_time DESC 
            LIMIT ? OFFSET ?
        ", [$limit, $offset]);
    }
    
    /**
     * Search visitors
     */
    public function searchVisitors($query, $limit = 50) {
        $searchTerm = "%$query%";
        return $this->db->fetchAll("
            SELECT ip_address, user_agent, page_visited, visit_time, country, city 
            FROM visitors 
            WHERE ip_address LIKE ? OR page_visited LIKE ? OR country LIKE ? OR city LIKE ?
            ORDER BY visit_time DESC 
            LIMIT ?
        ", [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
    }
    
    /**
     * Block IP address
     */
    public function blockIp($ip, $reason = '') {
        return $this->db->insert('blocked_ips', [
            'ip_address' => $ip,
            'reason' => $reason,
            'blocked_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Check if IP is blocked
     */
    public function isBlocked($ip) {
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM blocked_ips WHERE ip_address = ?", [$ip]);
        return $result['count'] > 0;
    }
    
    /**
     * Get comprehensive statistics
     */
    public function getComprehensiveStats() {
        return [
            'total_visitors' => $this->getTotalVisitors(),
            'online_users' => $this->getOnlineUsers(),
            'today_visitors' => $this->getTodayVisitors(),
            'week_visitors' => $this->getWeekVisitors(),
            'month_visitors' => $this->getMonthVisitors(),
            'most_visited_pages' => $this->getMostVisitedPages(5),
            'browser_stats' => $this->getBrowserStats(),
            'hourly_distribution' => $this->getHourlyDistribution()
        ];
    }
}
?>
