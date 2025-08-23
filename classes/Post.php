<?php
/**
 * Post Class
 * Handles blog post management
 */

class Post {
    private $conn;
    private $table_name = "posts";
    
    public $id;
    public $title;
    public $content;
    public $excerpt;
    public $featured_image;
    public $slug;
    public $status;
    public $author_id;
    public $view_count;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create new post
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (title, content, excerpt, featured_image, slug, status, author_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        // Generate slug if not provided
        if (empty($this->slug)) {
            $this->slug = $this->generateUniqueSlug($this->title);
        }
        
        // Generate excerpt if not provided
        if (empty($this->excerpt)) {
            $this->excerpt = $this->generateExcerpt($this->content);
        }
        
        if ($stmt->execute([$this->title, $this->content, $this->excerpt, $this->featured_image, $this->slug, $this->status, $this->author_id])) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Update post
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET title = ?, content = ?, excerpt = ?, featured_image = ?, slug = ?, status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        // Generate excerpt if not provided
        if (empty($this->excerpt)) {
            $this->excerpt = $this->generateExcerpt($this->content);
        }
        
        return $stmt->execute([$this->title, $this->content, $this->excerpt, $this->featured_image, $this->slug, $this->status, $this->id]);
    }
    
    // Delete post
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$this->id]);
    }
    
    // Get post by ID
    public function getById($id) {
        $query = "SELECT p.*, u.full_name as author_name FROM " . $this->table_name . " p 
                 LEFT JOIN users u ON p.author_id = u.id 
                 WHERE p.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->mapRowToProperties($row);
            return $row;
        }
        
        return false;
    }
    
    // Get post by slug
    public function getBySlug($slug) {
        $query = "SELECT p.*, u.full_name as author_name FROM " . $this->table_name . " p 
                 LEFT JOIN users u ON p.author_id = u.id 
                 WHERE p.slug = ? AND p.status = 'published' LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$slug]);
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->mapRowToProperties($row);
            
            // Increment view count
            $this->incrementViewCount($row['id']);
            
            return $row;
        }
        
        return false;
    }
    
    // Get all posts with pagination
    public function getAll($limit = 10, $offset = 0, $status = 'published', $search = '') {
        $query = "SELECT p.*, u.full_name as author_name,
                  (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) as like_count,
                  (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id AND c.status = 'approved') as comment_count
                  FROM " . $this->table_name . " p 
                  LEFT JOIN users u ON p.author_id = u.id 
                  WHERE 1=1";
        
        $params = [];
        
        if ($status !== 'all') {
            $query .= " AND p.status = ?";
            $params[] = $status;
        }
        
        if (!empty($search)) {
            $query .= " AND (p.title LIKE ? OR p.content LIKE ?)";
            $search_term = '%' . $search . '%';
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        $query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get total count
    public function getTotalCount($status = 'published', $search = '') {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE 1=1";
        $params = [];
        
        if ($status !== 'all') {
            $query .= " AND status = ?";
            $params[] = $status;
        }
        
        if (!empty($search)) {
            $query .= " AND (title LIKE ? OR content LIKE ?)";
            $search_term = '%' . $search . '%';
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get recent posts
    public function getRecent($limit = 5) {
        $query = "SELECT p.*, u.full_name as author_name FROM " . $this->table_name . " p 
                 LEFT JOIN users u ON p.author_id = u.id 
                 WHERE p.status = 'published' 
                 ORDER BY p.created_at DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get popular posts by view count
    public function getPopular($limit = 5) {
        $query = "SELECT p.*, u.full_name as author_name FROM " . $this->table_name . " p 
                 LEFT JOIN users u ON p.author_id = u.id 
                 WHERE p.status = 'published' 
                 ORDER BY p.view_count DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Increment view count
    public function incrementViewCount($post_id) {
        $query = "UPDATE " . $this->table_name . " SET view_count = view_count + 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$post_id]);
    }
    
    // Generate unique slug
    private function generateUniqueSlug($title) {
        $slug = create_slug($title);
        $original_slug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    // Check if slug exists
    private function slugExists($slug) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE slug = ?";
        if ($this->id) {
            $query .= " AND id != ?";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($this->id) {
            $stmt->execute([$slug, $this->id]);
        } else {
            $stmt->execute([$slug]);
        }
        
        return $stmt->rowCount() > 0;
    }
    
    // Generate excerpt from content
    private function generateExcerpt($content, $length = 150) {
        $content = strip_tags($content);
        if (strlen($content) <= $length) {
            return $content;
        }
        
        $excerpt = substr($content, 0, $length);
        $last_space = strrpos($excerpt, ' ');
        
        if ($last_space !== false) {
            $excerpt = substr($excerpt, 0, $last_space);
        }
        
        return $excerpt . '...';
    }
    
    // Map database row to object properties
    private function mapRowToProperties($row) {
        $this->id = $row['id'];
        $this->title = $row['title'];
        $this->content = $row['content'];
        $this->excerpt = $row['excerpt'];
        $this->featured_image = $row['featured_image'];
        $this->slug = $row['slug'];
        $this->status = $row['status'];
        $this->author_id = $row['author_id'];
        $this->view_count = $row['view_count'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
    }
    
    // Delete post by ID (static method for admin)
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
    
    // Get published post count
    public function getPublishedCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'published'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get draft post count
    public function getDraftCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'draft'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get total views across all posts
    public function getTotalViews() {
        $query = "SELECT SUM(view_count) as total_views FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_views'] ?? 0;
    }
    
    // Get today's views
    public function getTodayViews() {
        // This would require a views tracking table for accurate daily stats
        // For now, we'll return a simplified calculation
        $query = "SELECT SUM(view_count) as today_views FROM " . $this->table_name . " WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['today_views'] ?? 0;
    }
    
    // Get this month's post count
    public function getThisMonthCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Update post status
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    // Get views chart data for last N days
    public function getViewsChart($days = 30) {
        $query = "SELECT DATE(created_at) as date, SUM(view_count) as views 
                  FROM " . $this->table_name . " 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  GROUP BY DATE(created_at)
                  ORDER BY date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fill in missing days with 0 views
        $chart_data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chart_data[$date] = 0;
        }
        
        foreach ($results as $result) {
            $chart_data[$result['date']] = (int)$result['views'];
        }
        
        return $chart_data;
    }
    
    // Get posts chart data for last N days
    public function getPostsChart($days = 30) {
        $query = "SELECT DATE(created_at) as date, COUNT(*) as posts 
                  FROM " . $this->table_name . " 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  GROUP BY DATE(created_at)
                  ORDER BY date ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fill in missing days with 0 posts
        $chart_data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $chart_data[$date] = 0;
        }
        
        foreach ($results as $result) {
            $chart_data[$result['date']] = (int)$result['posts'];
        }
        
        return $chart_data;
    }
}

?>