<?php
date_default_timezone_set(timezoneId: 'Australia/Sydney');
class ChargingSession {
    private $db;
    
    public function __construct() {
        $this->db = new Database;
    }
    
    // Create a new charging session (check-in)
    public function checkIn($userId, $locationId) {
        // Prepare query
        $this->db->query('INSERT INTO charging_sessions (user_id, location_id, check_in_time) VALUES (:user_id, :location_id, NOW())');
        
        // Bind values
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':location_id', $locationId);
        
        // Execute
        if($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    // Complete a charging session (check-out)
    public function checkOut($sessionId) {
        // Get session details for cost calculation
        $this->db->query('SELECT cs.*, cl.cost_per_hour, cl.description
                         FROM charging_sessions cs
                         JOIN charging_locations cl ON cs.location_id = cl.location_id
                         WHERE cs.session_id = :session_id AND cs.status = "active"');
        $this->db->bind(':session_id', $sessionId);
        $session = $this->db->single();
        
        if (!$session) {
            return false;
        }
        
        // Calculate time difference and cost
        $checkInTime = new DateTime($session['check_in_time']);
        $checkOutTime = new DateTime(); // current time
        
        $interval = $checkInTime->diff($checkOutTime);
        print('interval: ' . $interval->format('%d days %h hours %i minutes'));
        $hours = $interval->h + ($interval->days * 24) + ($interval->i / 60);
        $totalCost = $hours * $session['cost_per_hour'];
        
        // Update session in database
        $this->db->query('UPDATE charging_sessions 
                         SET check_out_time = NOW(), 
                             total_cost = :total_cost, 
                             status = "completed" 
                         WHERE session_id = :session_id');
        
        $this->db->bind(':total_cost', $totalCost);
        $this->db->bind(':session_id', $sessionId);
        
        if($this->db->execute()) {
            return [
                'session_id' => $sessionId,
                'location' => $session['description'],
                'check_in_time' => $session['check_in_time'],
                'check_out_time' => $checkOutTime->format('Y-m-d H:i:s'),
                'hours' => number_format($hours, 2),
                'cost_per_hour' => $session['cost_per_hour'],
                'total_cost' => number_format($totalCost, 2)
            ];
        } else {
            return false;
        }
    }
    
    // Get active charging session for a user
    public function getActiveSession($userId) {
        $this->db->query('SELECT cs.*, cl.description, cl.cost_per_hour 
                         FROM charging_sessions cs 
                         JOIN charging_locations cl ON cs.location_id = cl.location_id 
                         WHERE cs.user_id = :user_id AND cs.status = "active"');
        $this->db->bind(':user_id', $userId);
        
        return $this->db->single();
    }
    
    // Get user's past sessions
    public function getUserPastSessions($userId) {
        $this->db->query('SELECT cs.*, cl.description 
                         FROM charging_sessions cs 
                         JOIN charging_locations cl ON cs.location_id = cl.location_id 
                         WHERE cs.user_id = :user_id AND cs.status = "completed" 
                         ORDER BY cs.check_out_time DESC');
        $this->db->bind(':user_id', $userId);
        
        return $this->db->resultSet();
    }
}