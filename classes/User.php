<?php
class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function emailExists($email) {
        $this->db->query("SELECT COUNT(*) as count FROM users WHERE email = ?");
        $this->db->bind("s", $email);
        $row = $this->db->single();
        return isset($row['count']) && $row['count'] > 0;
    }

    public function updateUser($data) {
        $this->db->query("UPDATE users SET name = ?, email = ?, phone = ? WHERE user_id = ?");
        $this->db->bind("sssi", $data['name'], $data['email'], $data['phone'], $data['id']);
        return $this->db->execute();
    }

    public function register($data) {
        $this->db->query("INSERT INTO users (name, email, phone, password, user_type) VALUES (?, ?, ?, ?, ?)");
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $this->db->bind("sssss", $data['name'], $data['email'], $data['phone'], $hashedPassword, $data['user_type']);
        return $this->db->execute();
    }

    public function updatePassword($data) {
        $this->db->query("UPDATE users SET password = ? WHERE user_id = ?");
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $this->db->bind("si", $hashedPassword, $data['user_id']);
        return $this->db->execute();
    }

    public function login($email, $password) {
        $this->db->query("SELECT * FROM users WHERE email = ?");
        $this->db->bind("s", $email);
        $row = $this->db->single();

        if ($row && password_verify($password, $row['password'])) {
            return $row;
        }
        return false;
    }

    public function findUserByEmail($email) {
        $this->db->query("SELECT * FROM users WHERE email = ?");
        $this->db->bind("s", $email);
        $row = $this->db->single();
        return $row !== null;
    }

    public function getTotalUsers() {
        $this->db->query("SELECT COUNT(*) AS total FROM users");
        $row = $this->db->single();
        return $row ? (int)$row['total'] : 0;
    }

    public function getUserById($id) {
        $this->db->query("SELECT * FROM users WHERE user_id = ?");
        $this->db->bind("i", $id);
        return $this->db->single();
    }

    public function getAllUsers() {
        $this->db->query("SELECT * FROM users WHERE user_type != 'Administrator' ORDER BY user_id");
        return $this->db->resultSet();
    }

    public function getUsersCheckedIn() {
        $this->db->query("SELECT u.*, cl.description, cs.check_in_time, cs.session_id, cl.cost_per_hour
                          FROM users u 
                          JOIN charging_sessions cs ON u.user_id = cs.user_id 
                          JOIN charging_locations cl ON cs.location_id = cl.location_id 
                          WHERE cs.status = 'active'
                          ORDER BY cs.check_in_time");
        return $this->db->resultSet();
    }
}
