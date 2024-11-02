<?php
// Database connection parameters
$servername = "database-1-instance-1.cpgoq8m2kfkd.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "Bagflea3!";
$dbname = "CraigslistDB";

// Create a MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
