<?php
/**
 * Initialization File
 * Maickel Okereke Professional Website
 */

// Start session with secure settings
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Set timezone
date_default_timezone_set('UTC');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration and functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// Autoload classes
spl_autoload_register(function ($class_name) {
    $class_file = __DIR__ . '/../classes/' . $class_name . '.php';
    if (file_exists($class_file)) {
        require_once $class_file;
    }
});

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die('Database connection failed. Please check your configuration.');
}

// Initialize main classes
$user = new User($db);
$post = new Post($db);
$comment = new Comment($db);
$like = new Like($db);
$message = new Message($db);

// Security helper function for admin area
function requireAdmin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        redirect(SITE_URL . '/admin/login.php');
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        redirect(SITE_URL . '/admin/login.php?timeout=1');
    }
    
    $_SESSION['last_activity'] = time();
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Get current user info
function getCurrentUser() {
    global $user;
    
    if (isLoggedIn()) {
        $user->getById($_SESSION['user_id']);
        return $user;
    }
    
    return null;
}

// Generate navigation menu
function getNavigation() {
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
    
    $nav_items = [
        'index' => ['title' => 'Home', 'url' => SITE_URL . '/index.php'],
        'blog' => ['title' => 'Blog', 'url' => SITE_URL . '/blog.php'],
        'contact' => ['title' => 'Contact', 'url' => SITE_URL . '/contact.php']
    ];
    
    return $nav_items;
}

// Get site statistics for admin dashboard
function getSiteStatistics() {
    global $post, $comment, $like, $message;
    
    $stats = [];
    
    // Posts statistics
    $stats['total_posts'] = $post->getTotalCount('all');
    $stats['published_posts'] = $post->getTotalCount('published');
    $stats['draft_posts'] = $post->getTotalCount('draft');
    
    // Comments statistics
    $stats['total_comments'] = $comment->getTotalCount('all');
    $stats['approved_comments'] = $comment->getTotalCount('approved');
    $stats['pending_comments'] = $comment->getTotalCount('pending');
    
    // Likes statistics
    $stats['total_likes'] = $like->getTotalCount();
    
    // Messages statistics
    $stats['total_messages'] = $message->getTotalCount();
    $stats['unread_messages'] = $message->getUnreadCount();
    
    return $stats;
}

// Include common header for public pages
function includeHeader($title = '', $description = '', $keywords = '') {
    $site_title = SITE_NAME;
    if (!empty($title)) {
        $site_title = $title . ' - ' . SITE_NAME;
    }
    
    if (empty($description)) {
        $description = 'Maickel Okereke - Professional Accountant and Web Developer. Expert in financial management and modern web development solutions.';
    }
    
    if (empty($keywords)) {
        $keywords = 'Maickel Okereke, accountant, web developer, financial management, web development, professional services';
    }
    
    include __DIR__ . '/../templates/header.php';
}

// Include common footer for public pages
function includeFooter() {
    include __DIR__ . '/../templates/footer.php';
}

// Include admin header
function includeAdminHeader($title = '') {
    $page_title = 'Admin Panel';
    if (!empty($title)) {
        $page_title = $title . ' - Admin Panel';
    }
    
    include __DIR__ . '/../admin/templates/header.php';
}

// Include admin footer
function includeAdminFooter() {
    include __DIR__ . '/../admin/templates/footer.php';
}

?>