<?php
// Database.php
require_once "config.php";

class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        global $host, $database, $username, $password;
        $this->host = $host;
        $this->dbname = $database;
        $this->username = $username;
        $this->password = $password;

        try {
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Koneksi database gagal: " . $e->getMessage();
			die(); // Terminate script execution on connection failure
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>