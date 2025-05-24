<?php
date_default_timezone_set('Australia/Sydney');

class ChargingSession {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function checkIn($userId, $locationId) {
        $this->db->query('INSERT INTO charging_sessions (user_id, location_id, check_in_time) VALUES (?, ?, NOW())');
        $this->db->bind("ii", $userId, $locationId);
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function checkOut($sessionId) {
        $this->db->query('SELECT cs.*, cl.cost_per_hour, cl.description
                         FROM charging_sessions cs
                         JOIN charging_locations cl ON cs.location_id = cl.location_id
                         WHERE cs.session_id = ? AND cs.status = "active"');
        $this->db->bind("i", $sessionId);
        $session = $this->db->single();

        if (!$session) return false;

        $checkInTime = new DateTime($session['check_in_time']);
        $checkOutTime = new DateTime();
        $interval = $checkInTime->diff($checkOutTime);

        $hours = $interval->h + ($interval->days * 24) + ($interval->i / 60);
        $totalCost = $hours * $session['cost_per_hour'];

        $this->db->query('UPDATE charging_sessions 
                         SET check_out_time = NOW(), 
                             total_cost = ?, 
                             status = "completed" 
                         WHERE session_id = ?');
        $this->db->bind("di", $totalCost, $sessionId);

        if ($this->db->execute()) {
            return [
                'session_id' => $sessionId,
                'location' => $session['description'],
                'check_in_time' => $session['check_in_time'],
                'check_out_time' => $checkOutTime->format('Y-m-d H:i:s'),
                'hours' => number_format($hours, 2),
                'cost_per_hour' => $session['cost_per_hour'],
                'total_cost' => number_format($totalCost, 2)
            ];
        }
        return false;
    }

    public function getActiveSession($userId) {
        $this->db->query('SELECT cs.*, cl.description, cl.cost_per_hour 
                         FROM charging_sessions cs 
                         JOIN charging_locations cl ON cs.location_id = cl.location_id 
                         WHERE cs.user_id = ? AND cs.status = "active"');
        $this->db->bind("i", $userId);
        return $this->db->single();
    }

    public function getAllActiveSessions() {
        $this->db->query('SELECT u.name AS user_name, cl.description AS location_name, cs.check_in_time 
                          FROM charging_sessions cs
                          JOIN users u ON cs.user_id = u.user_id
                          JOIN charging_locations cl ON cs.location_id = cl.location_id
                          WHERE cs.status = "active"
                          ORDER BY cs.check_in_time DESC');
        return $this->db->resultSet();
    }

    public function getTotalActiveSessions() {
        $this->db->query('SELECT COUNT(*) AS total FROM charging_sessions WHERE status = "active"');
        $row = $this->db->single();
        return $row ? (int)$row['total'] : 0;
    }

    public function getUserPastSessions($userId) {
        $this->db->query('SELECT cs.*, cl.description 
                         FROM charging_sessions cs 
                         JOIN charging_locations cl ON cs.location_id = cl.location_id 
                         WHERE cs.user_id = ? AND cs.status = "completed" 
                         ORDER BY cs.check_out_time DESC');
        $this->db->bind("i", $userId);
        return $this->db->resultSet();
    }

    public function getUserChargingSessions($userId) {
        $this->db->query("SELECT 
                    cs.session_id, 
                    cs.user_id, 
                    cs.location_id, 
                    cs.check_in_time AS start_time, 
                    cs.check_out_time AS end_time, 
                    cs.total_cost, 
                    cs.status, 
                    cl.description AS location_description, 
                    cl.cost_per_hour
                FROM 
                    charging_sessions cs
                JOIN 
                    charging_locations cl ON cs.location_id = cl.location_id
                WHERE 
                    cs.user_id = ?
                ORDER BY 
                    cs.check_in_time DESC");
        $this->db->bind("i", $userId);
        return $this->db->resultSet();
    }

    public function getUserActiveSession($userId) {
        $this->db->query("SELECT 
                    cs.session_id, 
                    cs.user_id, 
                    cs.location_id, 
                    cs.check_in_time AS start_time, 
                    cs.status, 
                    cl.description AS location_description, 
                    cl.cost_per_hour
                FROM 
                    charging_sessions cs
                JOIN 
                    charging_locations cl ON cs.location_id = cl.location_id
                WHERE 
                    cs.user_id = ? 
                    AND cs.status = 'active'
                LIMIT 1");
        $this->db->bind("i", $userId);
        return $this->db->single();
    }
}
