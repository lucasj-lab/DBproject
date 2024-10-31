<?php
session_start(); // Start the session

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to the login page (or any page you prefer)
header("Location: login.php");
exit();
?>
