<?php
/**
 * User Class
 * Handles user authentication and management
 */

class User {
    private $conn;
    private $table_name = "users";
    
    public $id;
    public $username;
    public $email;
    public $password;
    public $full_name;
    public $role;
    public $created_at;
    public $last_login;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Authenticate user
    public function login($username, $password) {
        $query = "SELECT id, username, email, password, full_name, role FROM " . $this->table_name . " WHERE username = ? OR email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username, $username]);
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->full_name = $row['full_name'];
                $this->role = $row['role'];
                
                // Update last login
                $this->updateLastLogin();
                
                return true;
            }
        }
        
        return false;
    }
    
    // Update last login timestamp
    private function updateLastLogin() {
        $query = "UPDATE " . $this->table_name . " SET last_login = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
    }
    
    // Create new user
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        // Hash password
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        
        if ($stmt->execute([$this->username, $this->email, $hashed_password, $this->full_name, $this->role])) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Check if username exists
    public function usernameExists($username) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username]);
        
        return $stmt->rowCount() > 0;
    }
    
    // Check if email exists
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        
        return $stmt->rowCount() > 0;
    }
    
    // Get user by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->full_name = $row['full_name'];
            $this->role = $row['role'];
            $this->created_at = $row['created_at'];
            $this->last_login = $row['last_login'];
            
            return true;
        }
        
        return false;
    }
    
    // Update user
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET username = ?, email = ?, full_name = ?";
        $params = [$this->username, $this->email, $this->full_name];
        
        if (!empty($this->password)) {
            $query .= ", password = ?";
            $params[] = password_hash($this->password, PASSWORD_DEFAULT);
        }
        
        $query .= " WHERE id = ?";
        $params[] = $this->id;
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute($params);
    }
    
    // Change password
    public function changePassword($old_password, $new_password) {
        $query = "SELECT password FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($old_password, $row['password'])) {
                $update_query = "UPDATE " . $this->table_name . " SET password = ? WHERE id = ?";
                $update_stmt = $this->conn->prepare($update_query);
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                return $update_stmt->execute([$hashed_password, $this->id]);
            }
        }
        
        return false;
    }
    
    // Delete user
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([$this->id]);
    }
    
    // Get all users
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>