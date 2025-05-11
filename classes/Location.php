<?php
class Location {
    private $db;
    
    public function __construct() {
        $this->db = new Database;
    }
    
    // Add new charging location
    public function addLocation($data) {
        // Prepare query
        $this->db->query('INSERT INTO charging_locations (description, num_stations, cost_per_hour) VALUES (:description, :num_stations, :cost_per_hour)');
        
        // Bind values
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':num_stations', $data['num_stations']);
        $this->db->bind(':cost_per_hour', $data['cost_per_hour']);
        
        // Execute
        if($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Update charging location
    public function updateLocation($data) {
        // Prepare query
        $this->db->query('UPDATE charging_locations SET description = :description, num_stations = :num_stations, cost_per_hour = :cost_per_hour WHERE location_id = :id');
        
        // Bind values
        $this->db->bind(':id', $data['location_id']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':num_stations', $data['num_stations']);
        $this->db->bind(':cost_per_hour', $data['cost_per_hour']);
        
        // Execute
        if($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Get all charging locations
    public function getAllLocations() {
        $this->db->query('SELECT * FROM charging_locations ORDER BY location_id');
        return $this->db->resultSet();
    }
    

    // Get total number of locations
    public function getTotalLocation() {
        $this->db->query(query: 'SELECT COUNT(*) AS total FROM charging_locations');
        $row = $this->db->single();
        return $row ? (int)$row['total'] : 0;
    }
    // Get locations with available stations
    public function getAvailableLocations() {
        $this->db->query('SELECT cl.*, 
                        (SELECT COUNT(*) FROM charging_sessions cs 
                         WHERE cs.location_id = cl.location_id AND cs.status = "active") 
                         AS active_sessions,
                        (cl.num_stations - (SELECT COUNT(*) FROM charging_sessions cs 
                                           WHERE cs.location_id = cl.location_id AND cs.status = "active")) 
                         AS available_stations
                        FROM charging_locations cl
                        HAVING available_stations > 0
                        ORDER BY cl.location_id');
        
        return $this->db->resultSet();
    }
    
    // Get locations that are full (no available stations)
    public function getFullLocations() {
        $this->db->query('SELECT cl.location_id, 
                         cl.description, 
                         cl.num_stations, 
                         cl.cost_per_hour,
                         (SELECT COUNT(*) FROM charging_sessions cs
                          WHERE cs.location_id = cl.location_id AND cs.status = "active")
                          AS active_sessions,
                         (cl.num_stations - (SELECT COUNT(*) FROM charging_sessions cs
                                            WHERE cs.location_id = cl.location_id AND cs.status = "active"))
                          AS available_stations
                        FROM charging_locations cl
                        ORDER BY cl.location_id');
                     
        return $this->db->resultSet();
    }
    
    // Get location by ID
    public function getLocationById($id):mixed {
        $this->db->query('SELECT * FROM charging_locations WHERE location_id = :id');
        $this->db->bind(':id', $id);
        
        return $this->db->single();
    }
    
    // Search locations
    public function searchLocations($searchTerm) {
        $this->db->query('SELECT * FROM charging_locations 
                         WHERE location_id LIKE :searchTerm 
                         OR description LIKE :searchTerm');
        $this->db->bind(':searchTerm', '%' . $searchTerm . '%');
        
        return $this->db->resultSet();
    }
    
    // Check if location is available
    public function isLocationAvailable($locationId) {
        $this->db->query('SELECT cl.num_stations, 
                        (SELECT COUNT(*) FROM charging_sessions cs 
                         WHERE cs.location_id = cl.location_id AND cs.status = "active") AS active_sessions
                        FROM charging_locations cl
                        WHERE cl.location_id = :locationId');
        
        $this->db->bind(':locationId', $locationId);
        $result = $this->db->single();
        
        if ($result) {
            return ($result['num_stations'] > $result['active_sessions']);
        }
        
        return false;
    }
    public function deleteLocation($id) {
        // First check if the location has any active sessions
        $this->db->query('SELECT COUNT(*) as count FROM charging_sessions 
                         WHERE location_id = :location_id AND status = "active"');
        $this->db->bind(':location_id', $id);
        $result = $this->db->single();
        
        if($result->count > 0) {
            // Location has active sessions, can't delete
            return false;
        }
        
        // No active sessions, proceed with deletion
        $this->db->query('DELETE FROM charging_locations WHERE location_id = :location_id');
        $this->db->bind(':location_id', $id);
        
        return $this->db->execute();
    }
}