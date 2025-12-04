<?php
/**
 * Test Real Form Submission
 */

// Start session and set up authentication
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'owner';
$_SESSION['user_name'] = 'Test Owner';

echo "<h2>Test Real User Creation Form</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Form submitted! Redirecting to controller...</h3>";
    
    // Redirect to the actual controller
    header('Location: /ergon-site/users/create');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test User Creation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 300px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <form method="POST" action="/ergon-site/users/create" enctype="multipart/form-data">
        <h3>Create New User</h3>
        
        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="name" value="Test User <?= time() ?>" required>
        </div>
        
        <div class="form-group">
            <label>Email Address *</label>
            <input type="email" name="email" value="test<?= time() ?>@example.com" required>
        </div>
        
        <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="phone" value="1234567890">
        </div>
        
        <div class="form-group">
            <label>Role *</label>
            <select name="role" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
                <option value="company_owner" selected>Company Owner</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Department ID</label>
            <input type="number" name="department_id" value="1">
        </div>
        
        <div class="form-group">
            <label>Designation</label>
            <input type="text" name="designation" value="Test Designation">
        </div>
        
        <div class="form-group">
            <label>Joining Date</label>
            <input type="date" name="joining_date" value="2024-01-01">
        </div>
        
        <div class="form-group">
            <label>Salary</label>
            <input type="number" name="salary" value="50000" step="0.01">
        </div>
        
        <div class="form-group">
            <label>Date of Birth</label>
            <input type="date" name="date_of_birth" value="1990-01-01">
        </div>
        
        <div class="form-group">
            <label>Gender</label>
            <select name="gender">
                <option value="">Select Gender</option>
                <option value="male" selected>Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Address</label>
            <textarea name="address">Test Address</textarea>
        </div>
        
        <div class="form-group">
            <label>Emergency Contact</label>
            <input type="text" name="emergency_contact" value="9876543210">
        </div>
        
        <button type="submit">Create User</button>
    </form>
    
    <hr>
    <p><a href="/ergon-site/users">Go to Users List</a></p>
    <p><a href="/ergon-site/users/create">Go to Create User Page</a></p>
</body>
</html>
