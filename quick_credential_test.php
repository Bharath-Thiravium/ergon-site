<?php
/**
 * Quick Database Credential Update Script
 * Use this to quickly test different database credentials
 */

echo "=== Quick Database Credential Tester ===\n\n";

// Prompt for database credentials
echo "Enter the database credentials from your Hostinger panel:\n\n";

echo "Database Host (usually 'localhost'): ";
$host = trim(fgets(STDIN));
if (empty($host)) $host = 'localhost';

echo "Database Name: ";
$database = trim(fgets(STDIN));

echo "Database Username: ";
$username = trim(fgets(STDIN));

echo "Database Password: ";
$password = trim(fgets(STDIN));

echo "\n=== Testing Connection ===\n";
echo "Host: $host\n";
echo "Database: $database\n";
echo "Username: $username\n";
echo "Password: " . (empty($password) ? '[EMPTY]' : '[SET]') . "\n\n";

try {
    $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Connection successful!\n";
    
    // Test basic query
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Database accessible - Found " . count($tables) . " tables\n";
    
    // Check for required tables
    $requiredTables = ['users', 'attendance', 'tasks', 'expenses', 'advances', 'leaves'];
    $missingTables = [];
    
    foreach ($requiredTables as $table) {
        if (!in_array($table, $tables)) {
            $missingTables[] = $table;
        }
    }
    
    if (empty($missingTables)) {
        echo "✅ All required tables found\n";
    } else {
        echo "⚠️ Missing tables: " . implode(', ', $missingTables) . "\n";
        echo "   You may need to import the database structure\n";
    }
    
    echo "\n=== Updating .env.production ===\n";
    
    // Update .env.production file
    $envContent = "# MySQL Database Configuration\n";
    $envContent .= "DB_HOST=$host\n";
    $envContent .= "DB_NAME=$database\n";
    $envContent .= "DB_USER=$username\n";
    $envContent .= "DB_PASS=$password\n\n";
    $envContent .= "# PostgreSQL Configuration\n";
    $envContent .= "SAP_PG_HOST=72.60.218.167\n";
    $envContent .= "SAP_PG_PORT=5432\n";
    $envContent .= "SAP_PG_DB=modernsap\n";
    $envContent .= "SAP_PG_USER=postgres\n";
    $envContent .= "SAP_PG_PASS=mango\n";
    
    file_put_contents('.env.production', $envContent);
    echo "✅ .env.production updated with working credentials\n";
    
    echo "\n=== Testing Admin Dashboard ===\n";
    echo "Now test: https://aes.athenas.co.in/ergon-site/admin/dashboard\n";
    echo "It should load without 500 errors!\n";
    
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n\n";
    
    echo "Common solutions:\n";
    echo "1. Double-check credentials in Hostinger panel\n";
    echo "2. Ensure database exists and user has permissions\n";
    echo "3. Try different host (mysql.hostinger.com instead of localhost)\n";
    echo "4. Check if subdomain needs separate database setup\n";
}

echo "\n=== Next Steps ===\n";
echo "1. If connection successful, test the admin dashboard\n";
echo "2. If still failing, check Hostinger subdomain database settings\n";
echo "3. Consider creating separate database for subdomain if needed\n";
?>