<?php
require_once '../includes/init.php';

// Set JSON header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!$input || !isset($input['post_id']) || !isset($input['csrf_token'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Verify CSRF token
if (!verify_csrf_token($input['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$post_id = (int)$input['post_id'];
$user_ip = get_client_ip();
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Validate post exists
if (!$post->getById($post_id)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit;
}

// Rate limiting
if (!rate_limit($user_ip . '_like', 10, 300)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests']);
    exit;
}

try {
    // Toggle like
    $result = $like->toggleLike($post_id, $user_ip, $user_agent);
    
    if ($result) {
        // Get updated like count and status
        $like_count = $like->getCountByPostId($post_id);
        $has_liked = $like->hasLiked($post_id, $user_ip);
        
        echo json_encode([
            'success' => true,
            'liked' => $has_liked,
            'like_count' => $like_count,
            'message' => $has_liked ? 'Article liké !' : 'Like retiré'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour du like'
        ]);
    }
} catch (Exception $e) {
    error_log('Like toggle error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur'
    ]);
}
?>