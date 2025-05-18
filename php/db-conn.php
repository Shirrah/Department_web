<?php
class Database {
    private static $instance = null;
    public $db;
    public $error;

    private function __construct() {
        if ($_SERVER['SERVER_NAME'] === 'localhost') {
            $host = "localhost";  
            $user = "root";
            $pass = "";
            $dbname = "u958767601_dcs";
        } else { 
            $host = "p:212.85.26.121"; 
            $user = "hpo-admin";
            $pass = "Shirrah+admin1234#";
            $dbname = "dcs";
        }
        $this->db = mysqli_init();

        $this->db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
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
