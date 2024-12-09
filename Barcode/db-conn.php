<?php
class Database {
    public $conn; // Change the visibility to public
    public $error; // Added property to store connection errors

    public function __construct() {
        // Establishing a connection to the database
        $this->db = new mysqli("auth-db1632.hstgr.io", "u958767601_shirrah", "Shirrah612345", "u958767601_dcs");

        // Checking for connection errors
        if ($this->conn->connect_error) {
            $this->error = "Connection failed: " . $this->conn->connect_error;
            die($this->error); // Terminating script execution if connection fails
        }
    }
}
?>
