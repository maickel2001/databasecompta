<?php
/**
 * Database Configuration
 * Maickel Okereke Professional Website
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'maickel_website';
    private $username = 'root';
    private $password = '';
    private $conn;
    
    public function __construct($host = null, $db_name = null, $username = null, $password = null) {
        if ($host) $this->host = $host;
        if ($db_name) $this->db_name = $db_name;
        if ($username) $this->username = $username;
        if ($password) $this->password = $password;
    }
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                )
            );
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            return false;
        }
        
        return $this->conn;
    }
}

// Configuration constants
define('SITE_URL', 'http://localhost/maickel-website');
define('SITE_NAME', 'Maickel Okereke - Professional Website');
define('ADMIN_EMAIL', 'maickel@example.com');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes

// Pagination settings
define('POSTS_PER_PAGE', 5);
define('COMMENTS_PER_PAGE', 10);

?>