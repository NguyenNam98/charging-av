<?php
class Location {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function addLocation($data) {
        $this->db->query('INSERT INTO charging_locations (description, num_stations, cost_per_hour) VALUES (?, ?, ?)');
        $this->db->bind("sid", $data['description'], $data['num_stations'], $data['cost_per_hour']);
        return $this->db->execute();
    }

    public function updateLocation($data) {
        $this->db->query('UPDATE charging_locations SET description = ?, num_stations = ?, cost_per_hour = ? WHERE location_id = ?');
        $this->db->bind("sidi", $data['description'], $data['num_stations'], $data['cost_per_hour'], $data['location_id']);
        return $this->db->execute();
    }

    public function getAllLocations() {
        $this->db->query('SELECT * FROM charging_locations ORDER BY location_id');
        return $this->db->resultSet();
    }

    public function getTotalLocation() {
        $this->db->query('SELECT COUNT(*) AS total FROM charging_locations');
        $row = $this->db->single();
        return $row ? (int)$row['total'] : 0;
    }

    public function getAvailableLocations() {
        $this->db->query('SELECT cl.*, 
                         (SELECT COUNT(*) FROM charging_sessions cs 
                          WHERE cs.location_id = cl.location_id AND cs.status = "active") AS active_sessions,
                         (cl.num_stations - (SELECT COUNT(*) FROM charging_sessions cs 
                                             WHERE cs.location_id = cl.location_id AND cs.status = "active")) AS available_stations
                          FROM charging_locations cl
                          HAVING available_stations > 0
                          ORDER BY cl.location_id');
        return $this->db->resultSet();
    }

    public function getFullLocations() {
        $this->db->query('SELECT cl.location_id, cl.description, cl.num_stations, cl.cost_per_hour,
                         (SELECT COUNT(*) FROM charging_sessions cs
                          WHERE cs.location_id = cl.location_id AND cs.status = "active") AS active_sessions,
                         (cl.num_stations - (SELECT COUNT(*) FROM charging_sessions cs
                                             WHERE cs.location_id = cl.location_id AND cs.status = "active")) AS available_stations
                          FROM charging_locations cl
                          ORDER BY cl.location_id');
        return $this->db->resultSet();
    }

    public function getLocationById($id): mixed {
        $this->db->query('SELECT * FROM charging_locations WHERE location_id = ?');
        $this->db->bind("i", $id);
        return $this->db->single();
    }

    public function searchLocations($searchTerm) {
        $like = "%$searchTerm%";
        $this->db->query('SELECT * FROM charging_locations 
                          WHERE location_id LIKE ? OR description LIKE ?');
        $this->db->bind("ss", $like, $like);
        return $this->db->resultSet();
    }

    public function isLocationAvailable($locationId) {
        $this->db->query('SELECT cl.num_stations, 
                         (SELECT COUNT(*) FROM charging_sessions cs 
                          WHERE cs.location_id = cl.location_id AND cs.status = "active") AS active_sessions
                          FROM charging_locations cl
                          WHERE cl.location_id = ?');
        $this->db->bind("i", $locationId);
        $result = $this->db->single();
        if ($result) {
            return ($result['num_stations'] > $result['active_sessions']);
        }
        return false;
    }

    public function deleteLocation($id) {
        $this->db->query('SELECT COUNT(*) as count FROM charging_sessions 
                          WHERE location_id = ? AND status = "active"');
        $this->db->bind("i", $id);
        $result = $this->db->single();
        if ($result && $result['count'] > 0) {
            return false; // Active sessions exist
        }

        $this->db->query('DELETE FROM charging_locations WHERE location_id = ?');
        $this->db->bind("i", $id);
        return $this->db->execute();
    }
}
