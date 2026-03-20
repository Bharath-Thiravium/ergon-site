<?php
echo "=== Database Credentials Diagnostic ===\n\n";

// Set subdomain environment
$_SERVER['HTTP_HOST'] = 'aes.athenas.co.in';
$_SERVER['HTTPS'] = 'on';

require_once 'app/config/environment.php';
echo "Environment: " . Environment::detect() . "\n\n";

// Test different credential combinations
$testCredentials = [
    [
        'name' => 'Production Credentials (from .env.production)',
        'host' => 'localhost',
        'database' => 'u494785662_ergon_site',
        'username' => 'u494785662_ergon_site',
        'password' => '@Admin@2025@'
    ],
    [
        'name' => 'Alternative Password 1',
        'host' => 'localhost',
        'database' => 'u494785662_ergon_site',
        'username' => 'u494785662_ergon_site',
        'password' => 'Admin@2025'
    ],
    [
        'name' => 'Alternative Password 2',
        'host' => 'localhost',
        'database' => 'u494785662_ergon_site',
        'username' => 'u494785662_ergon_site',
        'password' => 'admin2025'
    ],
    [
        'name' => 'Root Access Test',
        'host' => 'localhost',
        'database' => 'u494785662_ergon_site',
        'username' => 'root',
        'password' => ''
    ]
];

foreach ($testCredentials as $creds) {
    echo "Testing: " . $creds['name'] . "\n";
    echo "Host: " . $creds['host'] . "\n";
    echo "Database: " . $creds['database'] . "\n";
    echo "Username: " . $creds['username'] . "\n";
    echo "Password: " . (empty($creds['password']) ? '[EMPTY]' : '[SET]') . "\n";
    
    try {
        $dsn = "mysql:host={$creds['host']};dbname={$creds['database']};charset=utf8mb4";
        $pdo = new PDO($dsn, $creds['username'], $creds['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        echo "✅ Connection successful!\n";
        
        // Test a simple query
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users LIMIT 1");
        $result = $stmt->fetch();
        echo "✅ Query successful - Users table accessible\n";
        
        break; // Stop testing if we find working credentials
        
    } catch (PDOException $e) {
        echo "❌ Connection failed: " . $e->getMessage() . "\n";
    }
    
    echo "---\n";
}

echo "\n=== Environment Variables Check ===\n";
require_once 'app/config/database.php';

// Create a test database instance to see what values it's using
$db = new Database();
echo "Database configuration loaded from environment\n";

echo "\n=== Recommendation ===\n";
echo "If none of the credentials work, you may need to:\n";
echo "1. Check the correct database password in your hosting panel\n";
echo "2. Verify the database user exists and has proper permissions\n";
echo "3. Ensure the database server is running and accessible\n";
echo "4. Check if the database name is correct\n";
?>