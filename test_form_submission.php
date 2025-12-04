<?php
/**
 * Test Form Submission Debug
 */

// Start session
session_start();

// Set up basic session for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'owner';

echo "<h2>Form Submission Test</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Data Received:</h3>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    echo "<h3>Files Data:</h3>";
    echo "<pre>" . print_r($_FILES, true) . "</pre>";
    
    echo "<h3>Session Data:</h3>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    
    // Test the actual controller
    echo "<h3>Testing UsersController::create()</h3>";
    
    try {
        require_once __DIR__ . '/app/controllers/UsersController.php';
        $controller = new UsersController();
        
        echo "<p>Controller loaded successfully</p>";
        
        // Call the create method
        ob_start();
        $controller->create();
        $output = ob_get_clean();
        
        echo "<p>Controller executed. Output length: " . strlen($output) . "</p>";
        if (!empty($output)) {
            echo "<h4>Controller Output:</h4>";
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Controller Error: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} else {
    // Show test form
    ?>
    <form method="POST" enctype="multipart/form-data">
        <h3>Test User Creation Form</h3>
        
        <label>Name:</label><br>
        <input type="text" name="name" value="Test User" required><br><br>
        
        <label>Email:</label><br>
        <input type="email" name="email" value="test<?= time() ?>@example.com" required><br><br>
        
        <label>Phone:</label><br>
        <input type="tel" name="phone" value="1234567890"><br><br>
        
        <label>Role:</label><br>
        <select name="role">
            <option value="user">User</option>
            <option value="admin">Admin</option>
            <option value="company_owner" selected>Company Owner</option>
        </select><br><br>
        
        <label>Department ID:</label><br>
        <input type="number" name="department_id" value="1"><br><br>
        
        <label>Designation:</label><br>
        <input type="text" name="designation" value="Test Designation"><br><br>
        
        <label>Joining Date:</label><br>
        <input type="date" name="joining_date" value="2024-01-01"><br><br>
        
        <label>Salary:</label><br>
        <input type="number" name="salary" value="50000" step="0.01"><br><br>
        
        <label>Date of Birth:</label><br>
        <input type="date" name="date_of_birth" value="1990-01-01"><br><br>
        
        <label>Gender:</label><br>
        <select name="gender">
            <option value="">Select Gender</option>
            <option value="male" selected>Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select><br><br>
        
        <label>Address:</label><br>
        <textarea name="address">Test Address</textarea><br><br>
        
        <label>Emergency Contact:</label><br>
        <input type="text" name="emergency_contact" value="9876543210"><br><br>
        
        <button type="submit">Create User</button>
    </form>
    <?php
}
?>
