<?php
class Database {
    private $host = "localhost";
    private $db_name = "clearance_system";
    private $username = "root";
    private $password = "Amir&mysql_1738";
    private $port = "3306";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . 
                ";port=" . $this->port . 
                ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("SET NAMES utf8");
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "error" => "Database connection failed",
                "message" => $e->getMessage()
            ]);
            exit;
        }
        return $this->conn;
    }
}
?>