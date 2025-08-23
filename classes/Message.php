<?php
/**
 * Message Class
 * Handles contact form messages
 */

class Message {
    private $conn;
    private $table_name = "messages";
    
    public $id;
    public $name;
    public $email;
    public $subject;
    public $message;
    public $ip_address;
    public $status;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Create new message
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (name, email, subject, message, ip_address, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        // Set default status
        if (empty($this->status)) {
            $this->status = 'unread';
        }
        
        if ($stmt->execute([$this->name, $this->email, $this->subject, $this->message, $this->ip_address, $this->status])) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Update message status
    public function updateStatus($status) {
        $query = "UPDATE " . $this->table_name . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$status, $this->id]);
    }
    
    // Delete message
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$this->id]);
    }
    
    // Get message by ID
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
    
    // Get all messages with pagination
    public function getAll($limit = 20, $offset = 0, $status = 'all') {
        $query = "SELECT * FROM " . $this->table_name . " WHERE 1=1";
        $params = [];
        
        if ($status !== 'all') {
            $query .= " AND status = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get total message count
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
    
    // Get unread message count
    public function getUnreadCount() {
        return $this->getTotalCount('unread');
    }
    
    // Get recent messages
    public function getRecent($limit = 5) {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Mark as read
    public function markAsRead() {
        return $this->updateStatus('read');
    }
    
    // Mark as replied
    public function markAsReplied() {
        return $this->updateStatus('replied');
    }
    
    // Send email notification
    public function sendEmailNotification() {
        $to = ADMIN_EMAIL;
        $subject = "New Contact Form Message: " . $this->subject;
        
        $message_body = "
        <html>
        <head>
            <title>New Contact Form Message</title>
        </head>
        <body>
            <h2>New Contact Form Message</h2>
            <p><strong>Name:</strong> {$this->name}</p>
            <p><strong>Email:</strong> {$this->email}</p>
            <p><strong>Subject:</strong> {$this->subject}</p>
            <p><strong>IP Address:</strong> {$this->ip_address}</p>
            <p><strong>Date:</strong> {$this->created_at}</p>
            <hr>
            <h3>Message:</h3>
            <p>{$this->message}</p>
            <hr>
            <p><a href='" . SITE_URL . "/admin/messages.php?id={$this->id}'>View in Admin Panel</a></p>
        </body>
        </html>
        ";
        
        return send_email($to, $subject, $message_body);
    }
    
    // Send auto-reply to sender
    public function sendAutoReply() {
        $subject = "Thank you for contacting Maickel Okereke";
        
        $message_body = "
        <html>
        <head>
            <title>Thank you for your message</title>
        </head>
        <body>
            <h2>Thank you for contacting me!</h2>
            <p>Dear {$this->name},</p>
            <p>Thank you for reaching out to me. I have received your message regarding '{$this->subject}' and will get back to you as soon as possible.</p>
            <p>Your message is important to me, and I typically respond within 24-48 hours.</p>
            <p>Best regards,<br>Maickel Okereke<br>Accountant & Web Developer</p>
            <hr>
            <p><strong>Your original message:</strong></p>
            <p>{$this->message}</p>
        </body>
        </html>
        ";
        
        return send_email($this->email, $subject, $message_body);
    }
    
    // Check for spam
    public function isSpam() {
        // Check message content for spam
        if (is_spam($this->message) || is_spam($this->subject)) {
            return true;
        }
        
        // Check for suspicious patterns in name/email
        if (strlen($this->name) < 2 || strlen($this->name) > 100) {
            return true;
        }
        
        if (!validate_email($this->email)) {
            return true;
        }
        
        // Check for duplicate messages from same IP
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                 WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->ip_address]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] >= 3) { // More than 3 messages per hour from same IP
            return true;
        }
        
        return false;
    }
    
    // Get message statistics
    public function getStatistics() {
        $stats = [];
        
        // Total messages
        $stats['total'] = $this->getTotalCount();
        $stats['unread'] = $this->getTotalCount('unread');
        $stats['read'] = $this->getTotalCount('read');
        $stats['replied'] = $this->getTotalCount('replied');
        
        // Messages today
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['today'] = $result['count'];
        
        // Messages this week
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['this_week'] = $result['count'];
        
        // Messages this month
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['this_month'] = $result['count'];
        
        return $stats;
    }
    
    // Map database row to object properties
    private function mapRowToProperties($row) {
        $this->id = $row['id'];
        $this->name = $row['name'];
        $this->email = $row['email'];
        $this->subject = $row['subject'];
        $this->message = $row['message'];
        $this->ip_address = $row['ip_address'];
        $this->status = $row['status'];
        $this->created_at = $row['created_at'];
    }
    
    // Get unread message count
    public function getUnreadCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'unread'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get recent messages
    public function getRecent($limit = 5) {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Update message status
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$status, $id]);
    }
    
    // Delete message by ID
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
    
    // Get read message count
    public function getReadCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'read'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get archived message count
    public function getArchivedCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'archived'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get today's message count
    public function getTodayCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    // Get this week's message count
    public function getThisWeekCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE YEARWEEK(created_at) = YEARWEEK(NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}

?>