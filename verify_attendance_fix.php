<?php
/**
 * Verify Attendance Fix
 * This script verifies that the attendance table structure is correct
 * and the clock-in/out functionality should work
 */

require_once __DIR__ . '/app/config/database.php';

echo "========================================\n";
echo "Attendance Fix Verification\n";
echo "========================================\n\n";

try {
    $db = Database::connect();
    
    // Check table structure
    echo "1. Checking attendance table structure...\n";
    $stmt = $db->query("DESCRIBE attendance");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $requiredColumns = [
        'id' => 'int',
        'user_id' => 'int',
        'project_id' => 'int',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'location_name' => 'varchar',
        'location_type' => 'varchar',
        'location_title' => 'varchar',
        'location_radius' => 'int',
        'status' => 'varchar',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp'
    ];
    
    $foundColumns = [];
    foreach ($columns as $col) {
        $foundColumns[$col['Field']] = strtolower($col['Type']);
    }
    
    $allGood = true;
    foreach ($requiredColumns as $colName => $colType) {
        if (isset($foundColumns[$colName])) {
            $typeMatch = strpos($foundColumns[$colName], $colType) !== false;
            // Accept ENUM for status field
            if ($colName === 'status' && strpos($foundColumns[$colName], 'enum') !== false) {
                $typeMatch = true;
            }
            if ($typeMatch) {
                echo "   ✓ {$colName} ({$foundColumns[$colName]})\n";
            } else {
                echo "   ⚠ {$colName} exists but type mismatch: expected {$colType}, got {$foundColumns[$colName]}\n";
                $allGood = false;
            }
        } else {
            echo "   ✗ {$colName} is MISSING!\n";
            $allGood = false;
        }
    }
    
    // Check for old columns that should NOT exist
    echo "\n2. Checking for old columns (should not exist)...\n";
    $oldColumns = ['latitude', 'longitude', 'check_in_latitude', 'check_in_longitude', 'check_out_latitude', 'check_out_longitude'];
    $foundOldColumns = false;
    foreach ($oldColumns as $oldCol) {
        if (isset($foundColumns[$oldCol])) {
            echo "   ⚠ Found old column: {$oldCol} (should be removed)\n";
            $foundOldColumns = true;
        }
    }
    if (!$foundOldColumns) {
        echo "   ✓ No old columns found (good!)\n";
    }
    
    // Check indexes
    echo "\n3. Checking indexes...\n";
    $stmt = $db->query("SHOW INDEX FROM attendance");
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $requiredIndexes = ['idx_user_id', 'idx_check_in_date', 'idx_project_id', 'idx_location_type'];
    $foundIndexes = [];
    foreach ($indexes as $idx) {
        $foundIndexes[] = $idx['Key_name'];
    }
    
    foreach ($requiredIndexes as $idxName) {
        if (in_array($idxName, $foundIndexes)) {
            echo "   ✓ {$idxName}\n";
        } else {
            echo "   ⚠ {$idxName} is missing (optional but recommended)\n";
        }
    }
    
    // Check sample data
    echo "\n4. Checking sample attendance records...\n";
    $stmt = $db->query("SELECT COUNT(*) as count FROM attendance");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Total records: {$result['count']}\n";
    
    if ($result['count'] > 0) {
        $stmt = $db->query("SELECT * FROM attendance ORDER BY id DESC LIMIT 1");
        $latest = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "   Latest record:\n";
        echo "     - ID: {$latest['id']}\n";
        echo "     - User ID: {$latest['user_id']}\n";
        echo "     - Check In: {$latest['check_in']}\n";
        echo "     - Check Out: " . ($latest['check_out'] ?: 'Not clocked out') . "\n";
        echo "     - Location Type: " . ($latest['location_type'] ?: 'Not set') . "\n";
        echo "     - Location Title: " . ($latest['location_title'] ?: 'Not set') . "\n";
    }
    
    // Check settings table
    echo "\n5. Checking settings table...\n";
    $stmt = $db->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($settings) {
        echo "   ✓ Settings found\n";
        echo "     - Base Location: ({$settings['base_location_lat']}, {$settings['base_location_lng']})\n";
        echo "     - Attendance Radius: {$settings['attendance_radius']} meters\n";
        echo "     - Location Title: " . ($settings['location_title'] ?: 'Not set') . "\n";
    } else {
        echo "   ⚠ No settings found (will use defaults)\n";
    }
    
    // Final verdict
    echo "\n========================================\n";
    if ($allGood && !$foundOldColumns) {
        echo "✅ VERIFICATION PASSED!\n";
        echo "The attendance table structure is correct.\n";
        echo "Clock-in/out functionality should work properly.\n";
    } else {
        echo "⚠ VERIFICATION INCOMPLETE\n";
        echo "Some issues were found. Please run the fix script:\n";
        echo "  php run_attendance_fix.php\n";
    }
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
