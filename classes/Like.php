<?php
/**
 * Like Class
 * Handles post likes/reactions
 */

class Like {
    private $conn;
    private $table_name = "likes";
    
    public $id;
    public $post_id;
    public $ip_address;
    public $user_agent;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Add like
    public function addLike($post_id, $ip_address, $user_agent = '') {
        // Check if already liked
        if ($this->hasLiked($post_id, $ip_address)) {
            return false;
        }
        
        $query = "INSERT INTO " . $this->table_name . " (post_id, ip_address, user_agent) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([$post_id, $ip_address, $user_agent])) {
            $this->id = $this->conn->lastInsertId();
            $this->post_id = $post_id;
            $this->ip_address = $ip_address;
            $this->user_agent = $user_agent;
            return true;
        }
        
        return false;
    }
    
    // Remove like
    public function removeLike($post_id, $ip_address) {
        $query = "DELETE FROM " . $this->table_name . " WHERE post_id = ? AND ip_address = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$post_id, $ip_address]);
    }
    
    // Toggle like (add if not exists, remove if exists)
    public function toggleLike($post_id, $ip_address, $user_agent = '') {
        if ($this->hasLiked($post_id, $ip_address)) {
            return $this->removeLike($post_id, $ip_address);
        } else {
            return $this->addLike($post_id, $ip_address, $user_agent);
        }
    }
    
    // Check if user has liked a post
    public function hasLiked($post_id, $ip_address) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE post_id = ? AND ip_address = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$post_id, $ip_address]);
        
        return $stmt->rowCount() > 0;
    }
    
    // Get like count for a post
    public function getCountByPostId($post_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE post_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$post_id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get likes for a post with details
    public function getByPostId($post_id, $limit = 50, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE post_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$post_id, $limit, $offset]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get all likes for admin panel
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT l.*, p.title as post_title, p.slug as post_slug FROM " . $this->table_name . " l 
                 LEFT JOIN posts p ON l.post_id = p.id 
                 ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit, $offset]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get total likes count
    public function getTotalCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get most liked posts
    public function getMostLikedPosts($limit = 10) {
        $query = "SELECT p.*, COUNT(l.id) as like_count, u.full_name as author_name 
                 FROM posts p 
                 LEFT JOIN " . $this->table_name . " l ON p.id = l.post_id 
                 LEFT JOIN users u ON p.author_id = u.id 
                 WHERE p.status = 'published' 
                 GROUP BY p.id 
                 ORDER BY like_count DESC 
                 LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get recent likes
    public function getRecent($limit = 10) {
        $query = "SELECT l.*, p.title as post_title, p.slug as post_slug FROM " . $this->table_name . " l 
                 LEFT JOIN posts p ON l.post_id = p.id 
                 ORDER BY l.created_at DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Delete like by ID
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$this->id]);
    }
    
    // Get like by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->mapRowToProperties($row);
            return $row;
        }
        
        return false;
    }
    
    // Get like statistics
    public function getStatistics() {
        $stats = [];
        
        // Total likes
        $stats['total_likes'] = $this->getTotalCount();
        
        // Likes today
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['likes_today'] = $result['count'];
        
        // Likes this week
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['likes_this_week'] = $result['count'];
        
        // Likes this month
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['likes_this_month'] = $result['count'];
        
        return $stats;
    }
    
    // Clean old likes (optional, for data management)
    public function cleanOldLikes($days = 365) {
        $query = "DELETE FROM " . $this->table_name . " WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$days]);
    }
    
    // Map database row to object properties
    private function mapRowToProperties($row) {
        $this->id = $row['id'];
        $this->post_id = $row['post_id'];
        $this->ip_address = $row['ip_address'];
        $this->user_agent = $row['user_agent'];
        $this->created_at = $row['created_at'];
    }
}

?>