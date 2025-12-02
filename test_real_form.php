<?php
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Real Form Submission</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Form submitted to test script</h3>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    // Now submit to the real controller
    echo "<h3>Redirecting to real controller...</h3>";
    
    // Create a form that auto-submits to the real endpoint
    echo '<form id="realForm" method="POST" action="/ergon-site/users/create">';
    foreach ($_POST as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $subValue) {
                echo '<input type="hidden" name="' . htmlspecialchars($key) . '[]" value="' . htmlspecialchars($subValue) . '">';
            }
        } else {
            echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
        }
    }
    echo '</form>';
    echo '<script>document.getElementById("realForm").submit();</script>';
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Real Form</title>
</head>
<body>
    <h2>Test Real User Creation Form</h2>
    <p>This will submit to the actual UsersController</p>
    
    <form method="POST">
        <p>
            <label>Name:</label><br>
            <input type="text" name="name" value="Real Test Company Owner" required>
        </p>
        <p>
            <label>Email:</label><br>
            <input type="email" name="email" value="realtest<?= time() ?>@example.com" required>
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
            <textarea name="address">Real Test Address</textarea>
        </p>
        <p>
            <label>Emergency Contact:</label><br>
            <input type="text" name="emergency_contact" value="9876543210">
        </p>
        <p>
            <button type="submit">Submit to Real Controller</button>
        </p>
    </form>
</body>
</html>