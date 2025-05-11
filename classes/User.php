<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = new Database;
    }
    
    public function emailExists($email) {
        $query = "SELECT COUNT(*) FROM users WHERE email = :email";
        $this->db->query($query);
        $this->db->bind(':email', $email, PDO::PARAM_STR);
        $row = $this->db->single();
        return $row ? true : false;
    }

    public function updateUser($data) {

        $query = "UPDATE users SET name = :name, email = :email, phone = :phone WHERE user_id = :id";
        $this->db->query($query);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':id', $data['id']);
        if($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
    // Register user
    public function register($data) {
        // Prepare query
        $this->db->query('INSERT INTO users (name, email, phone, password, user_type) VALUES (:name, :email, :phone, :password, :user_type)');
        
        // Bind values
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind(':user_type', $data['user_type']);
        
        // Execute
        if($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

        /**
     * Update the user's password in the database.
     *
     * @param array $data Associative array containing 'id' and 'password'.
     * @return bool True on success, false on failure.
     */
    public function updatePassword($data) {

        $query = "UPDATE users SET password = :password WHERE user_id = :id";
        $this->db->query($query);
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind(':user_id', $data['id']);
          // Execute
          if($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Login user
    public function login($email, $password) {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        
        $row = $this->db->single();
        
        if($row) {
            $hashed_password = $row['password'];
            if(password_verify($password, $hashed_password)) {
                return $row;
            }
        }
        
        return false;
    }
    
    // Find user by email
    public function findUserByEmail($email) {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        
        $row = $this->db->single();
        
        // Check row
        if($this->db->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
    // get total users
    public function getTotalUsers() {
        $this->db->query('SELECT COUNT(*) AS total FROM users');
        $row = $this->db->single();
        return $row ? (int)$row['total'] : 0;
    }
    // Get user by ID
    public function getUserById($id) {
        $this->db->query('SELECT * FROM users WHERE user_id = :id');
        $this->db->bind(':id', $id);
        
        return $this->db->single();
    }
    
    // Get all users
    public function getAllUsers() {
        $this->db->query('SELECT * FROM users ORDER BY user_id');
        return $this->db->resultSet();
    }
    
    // Get users currently checked in
    public function getUsersCheckedIn() {
        $this->db->query('SELECT u.*, cl.description, cs.check_in_time, cs.session_id 
                         FROM users u 
                         JOIN charging_sessions cs ON u.user_id = cs.user_id 
                         JOIN charging_locations cl ON cs.location_id = cl.location_id 
                         WHERE cs.status = "active"
                         ORDER BY cs.check_in_time');
        
        return $this->db->resultSet();
    }
}