<?php
session_start();

// Simulate the exact UsersController create method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Debugging Actual User Creation Process</h2>";
    
    try {
        require_once __DIR__ . '/app/config/database.php';
        $db = Database::connect();
        
        echo "<h3>Step 1: Authentication Check</h3>";
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['owner', 'admin', 'company_owner'])) {
            echo "<p style='color: red;'>❌ Authentication failed</p>";
            exit;
        }
        echo "<p style='color: green;'>✅ Authentication passed</p>";
        
        echo "<h3>Step 2: Generate Employee ID</h3>";
        $employeeId = $_POST['employee_id'] ?? '';
        if (empty($employeeId)) {
            $stmt = $db->prepare("SELECT employee_id FROM users WHERE employee_id LIKE 'EMP%' ORDER BY employee_id DESC LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['employee_id']) {
                $lastNum = intval(substr($result['employee_id'], 3));
                $nextNum = $lastNum + 1;
            } else {
                $nextNum = 1;
            }
            
            $employeeId = 'EMP' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        }
        echo "<p>Generated Employee ID: $employeeId</p>";
        
        echo "<h3>Step 3: Age Validation</h3>";
        if (!empty($_POST['date_of_birth'])) {
            $dob = new DateTime($_POST['date_of_birth']);
            $today = new DateTime();
            $age = $today->diff($dob)->y;
            
            if ($age < 17) {
                echo "<p style='color: red;'>❌ Age validation failed: $age years old</p>";
                exit;
            }
            echo "<p style='color: green;'>✅ Age validation passed: $age years old</p>";
        } else {
            echo "<p style='color: blue;'>ℹ No date of birth provided</p>";
        }
        
        echo "<h3>Step 4: Password Generation</h3>";
        $tempPassword = 'PWD' . rand(1000, 9999);
        $hashedPassword = password_hash($tempPassword, PASSWORD_BCRYPT);
        echo "<p>Temp Password: $tempPassword</p>";
        
        echo "<h3>Step 5: Role Validation</h3>";
        $allowedRoles = ['user', 'admin', 'owner', 'company_owner', 'system_admin'];
        $role = $_POST['role'] ?? 'user';
        if (!in_array($role, $allowedRoles)) {
            $role = 'user';
        }
        echo "<p>Role: $role</p>";
        
        echo "<h3>Step 6: Department Processing</h3>";
        $departmentId = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
        echo "<p>Department ID: " . ($departmentId ?? 'NULL') . "</p>";
        
        echo "<h3>Step 7: Database Insertion</h3>";
        $stmt = $db->prepare("INSERT INTO users (employee_id, name, email, password, phone, role, status, department_id, designation, joining_date, salary, date_of_birth, gender, address, emergency_contact, created_at) VALUES (?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $params = [
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
        ];
        
        echo "<p>SQL Parameters:</p>";
        echo "<pre>" . print_r($params, true) . "</pre>";
        
        $result = $stmt->execute($params);
        
        if ($result) {
            $userId = $db->lastInsertId();
            echo "<p style='color: green;'>✅ User created successfully (ID: $userId)</p>";
            
            echo "<h3>Step 8: Session Credentials</h3>";
            $_SESSION['new_credentials'] = [
                'email' => $_POST['email'],
                'password' => $tempPassword,
                'employee_id' => $employeeId
            ];
            echo "<p style='color: green;'>✅ Credentials stored in session</p>";
            
            echo "<h3>✅ SUCCESS!</h3>";
            echo "<p><strong>User creation completed successfully!</strong></p>";
            echo "<p>The process should redirect to: /ergon-site/users?success=User created successfully</p>";
            
        } else {
            echo "<p style='color: red;'>❌ Database insertion failed</p>";
            $errorInfo = $stmt->errorInfo();
            echo "<pre>" . print_r($errorInfo, true) . "</pre>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Actual User Creation</title>
</head>
<body>
    <h2>Debug Actual User Creation Process</h2>
    <p>This simulates the exact same process as the UsersController</p>
    
    <form method="POST">
        <p>
            <label>Name:</label><br>
            <input type="text" name="name" value="Debug Company Owner" required>
        </p>
        <p>
            <label>Email:</label><br>
            <input type="email" name="email" value="debug<?= time() ?>@example.com" required>
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
                <option value="male" selected>Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </p>
        <p>
            <label>Address:</label><br>
            <textarea name="address">Debug Address</textarea>
        </p>
        <p>
            <label>Emergency Contact:</label><br>
            <input type="text" name="emergency_contact" value="9876543210">
        </p>
        <p>
            <button type="submit">Debug Create User</button>
        </p>
    </form>
    
    <hr>
    <p><strong>After this works, try the real form:</strong></p>
    <p><a href="/ergon-site/users/create">Real User Creation Form</a></p>
</body>
</html>