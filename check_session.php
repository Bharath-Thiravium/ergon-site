<?php
session_start();
echo "<h2>Session Status</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p>Role: " . ($_SESSION['role'] ?? 'Not set') . "</p>";
echo "<p>Username: " . ($_SESSION['username'] ?? 'Not set') . "</p>";

if (!isset($_SESSION['user_id'])) {
    echo "<p><strong>You need to login first.</strong></p>";
    echo "<p><a href='/ergon-site/login'>Go to Login</a></p>";
} elseif (!in_array($_SESSION['role'], ['admin', 'owner'])) {
    echo "<p><strong>You need admin or owner role to access project management.</strong></p>";
    echo "<p>Current role: " . $_SESSION['role'] . "</p>";
} else {
    echo "<p><strong>✅ You should be able to access project management.</strong></p>";
    echo "<p><a href='/ergon-site/project-management'>Go to Project Management</a></p>";
}
?>