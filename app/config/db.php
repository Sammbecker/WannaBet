<?php
/**
 * Database Connection Configuration
 * This file handles the database connection for the WannaBet application
 */

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        $dsn = "mysql:host=localhost;dbname=betting_app";
        $username = "root"; 
        $password = ""; // Default XAMPP MySQL password is empty
        
        try {
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

/**
 * Helper function to get database connection
 */
function getDB() {
    return Database::getInstance()->getConnection();
}
?> 