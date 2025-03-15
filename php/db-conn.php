<?php
class Database {
    private static $instance = null;
    public $db;
    public $error;

    private function __construct() {
        // Detect environment (Change this logic if needed)
        if ($_SERVER['SERVER_NAME'] === 'localhost') {
            // Local Development Database
            $host = "localhost";  // No persistent connection for local
            $user = "root";  // Default XAMPP/MAMP user
            $pass = "";  // Empty password for local MySQL
            $dbname = "u958767601_dcs";
        } else {
            // Production Database (Hostinger VPS)
            $host = "p:5.181.217.145";  // Persistent connection
            $user = "hpo-admin";
            $pass = "Shirrah+admin1234#";
            $dbname = "dcs";
        }

        // Create a MySQLi object
        $this->db = mysqli_init();

        // Set timeout before connecting (optional for production)
        $this->db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

        // Establish the connection
        if (!$this->db->real_connect($host, $user, $pass, $dbname)) {
            $this->error = "Connection failed: " . $this->db->connect_error;
            die($this->error);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
}
?>
