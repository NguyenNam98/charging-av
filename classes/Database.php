<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $conn;
    private $stmt;
    private $error;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);

        if ($this->conn->connect_error) {
            $this->error = $this->conn->connect_error;
            die('Connection Error: ' . $this->error);
        }
    }

    // Prepare statement with query
    public function query($query) {
        $this->stmt = $this->conn->prepare($query);
        if (!$this->stmt) {
            die('Query Error: ' . $this->conn->error);
        }
    }

    // Bind values (type must be specified like 's', 'i', 'd', 'b')
    public function bind($types, ...$params) {
        $this->stmt->bind_param($types, ...$params);
    }

    // Execute the prepared statement
    public function execute() {
        return $this->stmt->execute();
    }

    // Get result set as array
    public function resultSet() {
        $this->execute();
        $result = $this->stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get single record
    public function single() {
        $this->execute();
        $result = $this->stmt->get_result();
        return $result->fetch_assoc();
    }

    // Get row count
    public function rowCount() {
        return $this->stmt->affected_rows;
    }

    // Get last inserted ID
    public function lastInsertId() {
        return $this->conn->insert_id;
    }

    // Close connection
    public function close() {
        $this->stmt?->close();
        $this->conn->close();
    }
}
