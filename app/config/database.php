<?php
/**
 * Database Configuration
 * ergon - Employee Tracker & Task Manager
 */

require_once __DIR__ . '/environment.php';

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    private static $instance = null;
    
    public function __construct() {
        try {
            if (Environment::isDevelopment()) {
                $this->host = $_ENV['DB_HOST'] ?? 'localhost';
                $this->db_name = $_ENV['DB_NAME'] ?? 'ergon-site_db';
                $this->username = $_ENV['DB_USER'] ?? 'root';
                $this->password = $_ENV['DB_PASS'] ?? '';
            } else {
                $this->host = $_ENV['DB_HOST'] ?? 'localhost';
                $this->db_name = $_ENV['DB_NAME'] ?? 'u494785662_ergon_site';
                $this->username = $_ENV['DB_USER'] ?? 'u494785662_ergon_site';
                $this->password = $_ENV['DB_PASS'] ?? '@Admin@2025@';
            }
        } catch (Exception $e) {
            error_log('Database configuration error: ' . $e->getMessage());
            throw new Exception('Database configuration failed');
        }
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
            ];
            
            if (!Environment::isDevelopment()) {
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                $options[PDO::ATTR_TIMEOUT] = 30;
            }
            
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                $options
            );
        } catch(PDOException $e) {
            error_log("Connection error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
        
        return $this->conn;
    }
    
    public static function connect() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->getConnection();
    }
    
    public function getEnvironment() {
        return Environment::isDevelopment() ? 'development' : 'production';
    }
    
    public static function getPostgreSQLConfig() {
        // Load environment variables
        $envFile = __DIR__ . '/../../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }
        
        return [
            'host' => $_ENV['SAP_PG_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['SAP_PG_PORT'] ?? '5432',
            'database' => $_ENV['SAP_PG_DB'] ?? 'modernsap',
            'username' => $_ENV['SAP_PG_USER'] ?? 'postgres',
            'password' => $_ENV['SAP_PG_PASS'] ?? 'mango'
        ];
    }
}
?>
