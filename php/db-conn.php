<?php
class Database {
    private static $instance = null;
    public $db;
    public $error;

    private function __construct() {
        $this->db = new mysqli("auth-db1632.hstgr.io", "u958767601_shirrah", "Shirrah612345", "u958767601_dcs");

        if ($this->db->connect_error) {
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

    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>
