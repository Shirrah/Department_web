<?php
class Database {
    private static $instance = null;
    public $db;
    public $error;

    private function __construct() {
        // Use Persistent Connection (prevents excessive new connections)
        $host = "p:auth-db1632.hstgr.io";  // Persistent connection prefix "p:"
        $user = "u958767601_shirrah";
        $pass = "Shirrah612345";
        $dbname = "u958767601_dcs";

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
