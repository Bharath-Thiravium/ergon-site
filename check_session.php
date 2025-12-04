<?php
session_start();

echo "<h2>Session Debug</h2>";
echo "<h3>Current Session Data:</h3>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h3>Authentication Check:</h3>";
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ Not logged in - no user_id in session</p>";
} else {
    echo "<p style='color: green;'>✅ Logged in as user ID: " . $_SESSION['user_id'] . "</p>";
}

if (!isset($_SESSION['role'])) {
    echo "<p style='color: red;'>❌ No role in session</p>";
} else {
    echo "<p style='color: blue;'>👤 Role: " . $_SESSION['role'] . "</p>";
}

$allowedRoles = ['owner', 'admin', 'company_owner'];
if (isset($_SESSION['role']) && in_array($_SESSION['role'], $allowedRoles)) {
    echo "<p style='color: green;'>✅ Has permission to create users</p>";
} else {
    echo "<p style='color: red;'>❌ Does NOT have permission to create users</p>";
    echo "<p>Required roles: " . implode(', ', $allowedRoles) . "</p>";
}

echo "<h3>Quick Fix:</h3>";
echo "<p>If you need to login as owner/admin, you can:</p>";
echo "<ol>";
echo "<li>Go to <a href='/ergon-site/login'>/ergon-site/login</a></li>";
echo "<li>Or temporarily set session (for testing):</li>";
echo "</ol>";

if (isset($_GET['set_owner'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'owner';
    $_SESSION['name'] = 'Test Owner';
    echo "<p style='color: green;'>✅ Session set to owner for testing</p>";
    echo "<p><a href='/ergon-site/users/create'>Now try creating a user</a></p>";
}

if (!isset($_SESSION['user_id'])) {
    echo "<p><a href='?set_owner=1' style='background: #007cba; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>Set Test Owner Session</a></p>";
}
?>
