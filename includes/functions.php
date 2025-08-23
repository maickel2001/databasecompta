<?php
/**
 * Common Functions
 * Maickel Okereke Professional Website
 */

// Security functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function get_client_ip() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ip_keys as $key) {
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

// URL and slug functions
function create_slug($string) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    return $slug;
}

function redirect($url, $permanent = false) {
    if ($permanent) {
        header('HTTP/1.1 301 Moved Permanently');
    }
    header('Location: ' . $url);
    exit();
}

// Time and date functions
function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    $time = ($time < 1) ? 1 : $time;
    $tokens = array(
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '') . ' ago';
    }
}

// File upload functions
function upload_image($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        return ['success' => false, 'message' => 'File too large'];
    }

    // Create uploads directory if it doesn't exist
    if (!file_exists(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0777, true);
    }

    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $filepath = UPLOAD_PATH . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'url' => UPLOAD_URL . $filename];
    }

    return ['success' => false, 'message' => 'Failed to upload file'];
}

// Pagination functions
function paginate($total_records, $records_per_page, $current_page, $base_url) {
    $total_pages = ceil($total_records / $records_per_page);
    $pagination = '';

    if ($total_pages > 1) {
        $pagination .= '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        
        // Previous button
        if ($current_page > 1) {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $base_url . '?page=' . ($current_page - 1) . '">Previous</a></li>';
        }
        
        // Page numbers
        for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++) {
            $active = ($i == $current_page) ? ' active' : '';
            $pagination .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $base_url . '?page=' . $i . '">' . $i . '</a></li>';
        }
        
        // Next button
        if ($current_page < $total_pages) {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $base_url . '?page=' . ($current_page + 1) . '">Next</a></li>';
        }
        
        $pagination .= '</ul></nav>';
    }

    return $pagination;
}

// Email function
function send_email($to, $subject, $message, $from_name = 'Maickel Okereke', $from_email = ADMIN_EMAIL) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: {$from_name} <{$from_email}>" . "\r\n";
    $headers .= "Reply-To: {$from_email}" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Anti-spam functions
function is_spam($content) {
    $spam_patterns = [
        '/\b(?:viagra|cialis|pharmacy|casino|poker|lottery)\b/i',
        '/\b(?:buy now|click here|free money|make money fast)\b/i',
        '/(?:http[s]?:\/\/){2,}/',
        '/[A-Z]{10,}/'
    ];
    
    foreach ($spam_patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            return true;
        }
    }
    
    return false;
}

function rate_limit($identifier, $max_attempts = 5, $time_window = 300) {
    session_start();
    $key = 'rate_limit_' . md5($identifier);
    $current_time = time();
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 1, 'start_time' => $current_time];
        return true;
    }
    
    $data = $_SESSION[$key];
    
    if ($current_time - $data['start_time'] > $time_window) {
        $_SESSION[$key] = ['count' => 1, 'start_time' => $current_time];
        return true;
    }
    
    if ($data['count'] >= $max_attempts) {
        return false;
    }
    
    $_SESSION[$key]['count']++;
    return true;
}

?>