<?php
session_start();

echo "<h2>Session Debug</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";
echo "Session Save Path: " . session_save_path() . "<br>";

echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Cookies:</h3>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

if (isset($_SESSION['user_id'])) {
    echo "✅ User is logged in: " . $_SESSION['user_id'];
} else {
    echo "❌ User not logged in";
}
?>