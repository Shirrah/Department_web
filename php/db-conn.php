<?php
class Database {
    public $db; // Change the visibility to public
    public $error; // Added property to store connection errors

    public function __construct() {
        // Establishing a connection to the database
        $this->db = new mysqli("localhost", "root", "", "dcs");

        // Checking for connection errors
        if ($this->db->connect_error) {
            $this->error = "Connection failed: " . $this->db->connect_error;
            die($this->error); // Terminating script execution if connection fails
        }
    }
}
?>
