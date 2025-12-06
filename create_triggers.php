<?php
require_once __DIR__ . '/app/config/database.php';
$db = Database::connect();

// Drop existing triggers if they exist
try {
    $db->exec("DROP TRIGGER IF EXISTS sync_attendance_before_insert");
    $db->exec("DROP TRIGGER IF EXISTS sync_attendance_before_update");
} catch (Exception $e) {
    // Ignore errors
}

// Create INSERT trigger
$trigger1 = "
CREATE TRIGGER sync_attendance_before_insert
BEFORE INSERT ON attendance
FOR EACH ROW
BEGIN
    IF NEW.clock_in IS NOT NULL AND NEW.check_in IS NULL THEN
        SET NEW.check_in = NEW.clock_in;
    END IF;
    IF NEW.clock_out IS NOT NULL AND NEW.check_out IS NULL THEN
        SET NEW.check_out = NEW.clock_out;
    END IF;
    IF NEW.check_in IS NOT NULL AND NEW.clock_in IS NULL THEN
        SET NEW.clock_in = NEW.check_in;
    END IF;
    IF NEW.check_out IS NOT NULL AND NEW.clock_out IS NULL THEN
        SET NEW.clock_out = NEW.check_out;
    END IF;
END
";

// Create UPDATE trigger
$trigger2 = "
CREATE TRIGGER sync_attendance_before_update
BEFORE UPDATE ON attendance
FOR EACH ROW
BEGIN
    IF NEW.clock_in IS NOT NULL AND NEW.check_in IS NULL THEN
        SET NEW.check_in = NEW.clock_in;
    END IF;
    IF NEW.clock_out IS NOT NULL AND NEW.check_out IS NULL THEN
        SET NEW.check_out = NEW.clock_out;
    END IF;
    IF NEW.check_in IS NOT NULL AND NEW.clock_in IS NULL THEN
        SET NEW.clock_in = NEW.check_in;
    END IF;
    IF NEW.check_out IS NOT NULL AND NEW.clock_out IS NULL THEN
        SET NEW.clock_out = NEW.check_out;
    END IF;
END
";

try {
    $db->exec($trigger1);
    echo "✓ Created INSERT trigger\n";
} catch (Exception $e) {
    echo "✗ INSERT trigger error: " . $e->getMessage() . "\n";
}

try {
    $db->exec($trigger2);
    echo "✓ Created UPDATE trigger\n";
} catch (Exception $e) {
    echo "✗ UPDATE trigger error: " . $e->getMessage() . "\n";
}

echo "\nTriggers created successfully!\n";
echo "Now clock_in and check_in will always stay in sync.\n";
