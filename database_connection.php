<?php
// Database connection parameters
$servername = "database-1-instance-1.ctkqko2k074a.us-east-2.rds.amazonaws.com";
$username = "admin";
$password = "Butterball3!";
$dbname = "DBproject";

// Create a MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
