<?php
/**
 * Comment Class
 * Handles comment management with threaded replies
 */

class Comment {
    private $conn;
    private $table_name = "comments";
    
    public $id;
    public $post_id;
    public $parent_id;
    public $author_name;
    public $author_email;
    public $author_avatar;
    public $content;
    public $ip_address;
    public $status;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create new comment
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (post_id, parent_id, author_name, author_email, author_avatar, content, ip_address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        // Set default status to approved (can be changed for moderation)
        if (empty($this->status)) {
            $this->status = 'approved';
        }
        
        if ($stmt->execute([$this->post_id, $this->parent_id, $this->author_name, $this->author_email, $this->author_avatar, $this->content, $this->ip_address, $this->status])) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Update comment
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET author_name = ?, author_email = ?, content = ?, status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$this->author_name, $this->author_email, $this->content, $this->status, $this->id]);
    }
    
    // Delete comment
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$this->id]);
    }
    
    // Get comment by ID
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
    
    // Get comments for a post with threaded structure
    public function getByPostId($post_id, $status = 'approved') {
        // First get all comments for the post
        $query = "SELECT * FROM " . $this->table_name . " WHERE post_id = ? AND status = ? ORDER BY created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$post_id, $status]);
        
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Organize into threaded structure
        return $this->buildCommentTree($comments);
    }
    
    // Get comments for admin panel with pagination
    public function getAll($limit = 20, $offset = 0, $status = 'all') {
        $query = "SELECT c.*, p.title as post_title FROM " . $this->table_name . " c 
                 LEFT JOIN posts p ON c.post_id = p.id 
                 WHERE 1=1";
        
        $params = [];
        
        if ($status !== 'all') {
            $query .= " AND c.status = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get total comment count
    public function getTotalCount($status = 'all') {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $params = [];
        
        if ($status !== 'all') {
            $query .= " WHERE status = ?";
            $params[] = $status;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get recent comments
    public function getRecent($limit = 5) {
        $query = "SELECT c.*, p.title as post_title, p.slug as post_slug FROM " . $this->table_name . " c 
                 LEFT JOIN posts p ON c.post_id = p.id 
                 WHERE c.status = 'approved' 
                 ORDER BY c.created_at DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Change comment status
    public function changeStatus($status) {
        $query = "UPDATE " . $this->table_name . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$status, $this->id]);
    }
    
    // Build threaded comment tree
    private function buildCommentTree($comments, $parent_id = null) {
        $tree = array();
        
        foreach ($comments as $comment) {
            if ($comment['parent_id'] == $parent_id) {
                $comment['replies'] = $this->buildCommentTree($comments, $comment['id']);
                $tree[] = $comment;
            }
        }
        
        return $tree;
    }
    
    // Get comment count for a post
    public function getCountByPostId($post_id, $status = 'approved') {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE post_id = ? AND status = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$post_id, $status]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Check for spam
    public function isSpam($content, $email = '') {
        // Basic spam detection
        if (is_spam($content)) {
            return true;
        }
        
        // Check for duplicate content
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE content = ? AND author_email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$content, $email]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }
    
    // Upload avatar
    public function uploadAvatar($file) {
        $upload_result = upload_image($file, ['jpg', 'jpeg', 'png', 'gif']);
        
        if ($upload_result['success']) {
            return $upload_result['filename'];
        }
        
        return false;
    }
    
    // Get gravatar URL
    public function getGravatarUrl($email, $size = 80) {
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=identicon";
    }
    
    // Get avatar URL (uploaded or gravatar)
    public function getAvatarUrl($avatar_filename, $email, $size = 80) {
        if (!empty($avatar_filename) && file_exists(UPLOAD_PATH . $avatar_filename)) {
            return UPLOAD_URL . $avatar_filename;
        }
        
        return $this->getGravatarUrl($email, $size);
    }
    
    // Map database row to object properties
    private function mapRowToProperties($row) {
        $this->id = $row['id'];
        $this->post_id = $row['post_id'];
        $this->parent_id = $row['parent_id'];
        $this->author_name = $row['author_name'];
        $this->author_email = $row['author_email'];
        $this->author_avatar = $row['author_avatar'];
        $this->content = $row['content'];
        $this->ip_address = $row['ip_address'];
        $this->status = $row['status'];
        $this->created_at = $row['created_at'];
    }
    
    // Get total comment count
    public function getTotalCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get pending comment count
    public function getPendingCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get recent comments
    public function getRecent($limit = 5) {
        $query = "SELECT c.*, p.title as post_title FROM " . $this->table_name . " c 
                 LEFT JOIN posts p ON c.post_id = p.id 
                 ORDER BY c.created_at DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get this month's comment count
    public function getThisMonthCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get comment count by post ID
    public function getCountByPostId($post_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE post_id = ? AND status = 'approved'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$post_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get comments chart data for last N days
    public function getCommentsChart($days = 30) {
        $query = "SELECT DATE(created_at) as date, COUNT(*) as comments 
                  FROM " . $this->table_name . " 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  GROUP BY DATE(created_at)
                  ORDER BY date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fill in missing days with 0 comments
        $chart_data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chart_data[$date] = 0;
        }
        
        foreach ($results as $result) {
            $chart_data[$result['date']] = (int)$result['comments'];
        }
        
        return $chart_data;
    }
    
    // Update comment status
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$status, $id]);
    }
    
    // Delete comment by ID
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
    
    // Get approved comment count
    public function getApprovedCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'approved'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get rejected comment count
    public function getRejectedCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'rejected'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get spam comment count
    public function getSpamCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'spam'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}

?>