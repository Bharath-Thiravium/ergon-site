<?php
/**
 * Test Form Submission for User Creation
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Form Submission Debug</h2>";
    echo "<h3>POST Data Received:</h3>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    echo "<h3>FILES Data:</h3>";
    echo "<pre>" . print_r($_FILES, true) . "</pre>";
    
    // Test the actual user creation process
    require_once __DIR__ . '/app/config/database.php';
    require_once __DIR__ . '/app/models/User.php';
    
    try {
        $db = Database::connect();
        
        // Simulate the exact same process as UsersController
        $employeeId = 'TEST' . rand(100, 999);
        $tempPassword = 'PWD' . rand(1000, 9999);
        $hashedPassword = password_hash($tempPassword, PASSWORD_BCRYPT);
        
        $departmentId = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
        $role = $_POST['role'] ?? 'user';
        
        echo "<h3>Processing with:</h3>";
        echo "<p>Employee ID: $employeeId</p>";
        echo "<p>Role: $role</p>";
        echo "<p>Department ID: $departmentId</p>";
        
        $stmt = $db->prepare("
            INSERT INTO users (
                employee_id, name, email, password, phone, role, status, 
                department_id, designation, joining_date, salary, 
                date_of_birth, gender, address, emergency_contact, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            $employeeId,
            trim($_POST['name'] ?? ''),
            trim($_POST['email'] ?? ''),
            $hashedPassword,
            trim($_POST['phone'] ?? ''),
            $role,
            $departmentId,
            trim($_POST['designation'] ?? ''),
            !empty($_POST['joining_date']) ? $_POST['joining_date'] : null,
            !empty($_POST['salary']) ? floatval($_POST['salary']) : null,
            !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
            $_POST['gender'] ?? null,
            trim($_POST['address'] ?? ''),
            trim($_POST['emergency_contact'] ?? '')
        ]);
        
        if ($result) {
            $userId = $db->lastInsertId();
            echo "<p style='color: green;'>✓ User created successfully (ID: $userId)</p>";
            echo "<p>Temp Password: $tempPassword</p>";
            
            // Don't clean up so you can verify in database
            echo "<p><strong>User created and saved in database!</strong></p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create user</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test User Creation Form</title>
</head>
<body>
    <h2>Test User Creation Form</h2>
    <form method="POST">
        <p>
            <label>Name:</label><br>
            <input type="text" name="name" value="Test Company Owner" required>
        </p>
        <p>
            <label>Email:</label><br>
            <input type="email" name="email" value="test<?= time() ?>@example.com" required>
        </p>
        <p>
            <label>Phone:</label><br>
            <input type="text" name="phone" value="1234567890">
        </p>
        <p>
            <label>Role:</label><br>
            <select name="role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
                <option value="owner">Owner</option>
                <option value="company_owner" selected>Company Owner</option>
            </select>
        </p>
        <p>
            <label>Department ID:</label><br>
            <input type="number" name="department_id" value="">
        </p>
        <p>
            <label>Designation:</label><br>
            <input type="text" name="designation" value="CEO">
        </p>
        <p>
            <label>Joining Date:</label><br>
            <input type="date" name="joining_date" value="2024-01-01">
        </p>
        <p>
            <label>Salary:</label><br>
            <input type="number" name="salary" value="100000" step="0.01">
        </p>
        <p>
            <label>Date of Birth:</label><br>
            <input type="date" name="date_of_birth" value="1980-01-01">
        </p>
        <p>
            <label>Gender:</label><br>
            <select name="gender">
                <option value="">Select</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </p>
        <p>
            <label>Address:</label><br>
            <textarea name="address">Test Address</textarea>
        </p>
        <p>
            <label>Emergency Contact:</label><br>
            <input type="text" name="emergency_contact" value="9876543210">
        </p>
        <p>
            <button type="submit">Create User</button>
        </p>
    </form>
</body>
</html>