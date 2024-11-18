<?php
class Database {
    public $conn;  // Public property to store the database connection
    public $error; // Public property to store any connection errors

    public function __construct() {
        // Connection to the database
        $this->conn = new mysqli("localhost", "root", "", "dcs");

        // Checking for connection errors
        if ($this->conn->connect_error) {
            // Storing the error message in the class property and terminating the script
            $this->error = "Connection failed: " . $this->conn->connect_error;
            die($this->error);
        }
    }
}
?>
