<?php
class Database {
    private static $instance = null;
    public $db;
    public $error;

    private function __construct() {
        // Use Persistent Connection (prevents excessive new connections)
        $host = "p:5.181.217.145";  // Persistent connection prefix "p:"
        $user = "hpo-admin";
        $pass = "Shirrah+admin1234#";
        $dbname = "dcs";

        // Create MySQLi Object
        $this->db = new mysqli($host, $user, $pass, $dbname);

        // Check for Connection Error
        if ($this->db->connect_error) {
            $this->error = "Connection failed: " . $this->db->connect_error;
            die($this->error);
        }

        // Set MySQL Timeout Before Connecting
        $this->db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
}
?>
