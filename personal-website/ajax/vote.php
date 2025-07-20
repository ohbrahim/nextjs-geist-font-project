<?php
/**
 * Poll Voting Handler - Personal Website
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

// Get poll ID
$pollId = intval($_POST['poll_id'] ?? 0);
$ipAddress = Security::getClientIp();

// Validate poll
$poll = $db->fetchOne("SELECT * FROM polls WHERE id = ? AND is_active = 1", [$pollId]);
if (!$poll) {
    echo json_encode(['success' => false, 'message' => 'الاستفتاء غير موجود أو غير نشط']);
    exit;
}

// Check if user has already voted
$hasVoted = $db->fetchOne("SELECT COUNT(*) as count FROM poll_votes WHERE poll_id = ? AND ip_address = ?", 
    [$pollId, $ipAddress]);

if ($hasVoted['count'] > 0) {
    echo json_encode(['success' => false, 'message' => 'لقد قمت بالتصويت مسبقاً']);
    exit;
}

// Handle voting
if ($poll['allow_multiple']) {
    // Multiple choice
    $options = $_POST['options'] ?? [];
    if (!is_array($options) || empty($options)) {
        echo json_encode(['success' => false, 'message' => 'يرجى اختيار خيار واحد على الأقل']);
        exit;
    }
    
    foreach ($options as $optionId) {
        $optionId = intval($optionId);
        
        // Validate option
        $option = $db->fetchOne("SELECT * FROM poll_options WHERE id = ? AND poll_id = ?", 
            [$optionId, $pollId]);
        
        if ($option) {
            // Record vote
            $db->insert('poll_votes', [
                'poll_id' => $pollId,
                'option_id' => $optionId,
                'ip_address' => $ipAddress
            ]);
            
            // Update vote count
            $db->update('poll_options', 
                ['vote_count' => $option['vote_count'] + 1], 
                'id = ?', 
                [$optionId]);
        }
    }
} else {
    // Single choice
    $optionId = intval($_POST['option_id'] ?? 0);
    
    if (!$optionId) {
        echo json_encode(['success' => false, 'message' => 'يرجى اختيار خيار']);
        exit;
    }
    
    // Validate option
    $option = $db->fetchOne("SELECT * FROM poll_options WHERE id = ? AND poll_id = ?", 
        [$optionId, $pollId]);
    
    if (!$option) {
        echo json_encode(['success' => false, 'message' => 'الخيار غير صالح']);
        exit;
    }
    
    // Record vote
    $db->insert('poll_votes', [
        'poll_id' => $pollId,
        'option_id' => $optionId,
        'ip_address' => $ipAddress
    ]);
    
    // Update vote count
    $db->update('poll_options', 
        ['vote_count' => $option['vote_count'] + 1], 
        'id = ?', 
        [$optionId]);
}

// Get updated results
$options = $db->fetchAll("SELECT * FROM poll_options WHERE poll_id = ? ORDER BY display_order ASC", [$pollId]);
$totalVotes = array_sum(array_column($options, 'vote_count'));

// Generate results HTML
$html = '<div class="poll-results">';
foreach ($options as $option) {
    $percentage = $totalVotes > 0 ? round(($option['vote_count'] / $totalVotes) * 100, 1) : 0;
    $html .= '
        <div class="poll-result-item">
            <div class="poll-option-text">' . htmlspecialchars($option['option_text']) . '</div>
            <div class="poll-progress">
                <div class="poll-progress-bar" style="width: ' . $percentage . '%"></div>
            </div>
            <div class="poll-stats">
                <span>' . $option['vote_count'] . ' صوت</span>
                <span>(' . $percentage . '%)</span>
            </div>
        </div>
    ';
}
$html .= '</div>';

echo json_encode([
    'success' => true,
    'results' => $html,
    'total_votes' => $totalVotes
]);
?>
