<?php
session_start();

echo "<h2>Session Debug</h2>";
echo "<p>Current Role: " . ($_SESSION['role'] ?? 'NOT SET') . "</p>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "</p>";
echo "<p>Username: " . ($_SESSION['username'] ?? 'NOT SET') . "</p>";

echo "<h3>Full Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check if role condition would pass
$role = $_SESSION['role'] ?? '';
$canCreateOwner = in_array($role, ['owner', 'admin']);
echo "<h3>Role Check:</h3>";
echo "<p>Can create owner/company_owner: " . ($canCreateOwner ? 'YES' : 'NO') . "</p>";
echo "<p>Role is 'owner': " . ($role === 'owner' ? 'YES' : 'NO') . "</p>";
echo "<p>Role is 'admin': " . ($role === 'admin' ? 'YES' : 'NO') . "</p>";
?>
