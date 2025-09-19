<?php
class Database {
    private $host = 'localhost';
    private $db   = 'social_network';
    private $user = 'root';
    private $pass = '';
    public $conn;

    public function __construct(){
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);
        if ($this->conn->connect_error) {
            die('DB Connection failed: ' . $this->conn->connect_error);
        }
        $this->conn->set_charset('utf8mb4');
    }
}
?>
