<?php
$servername = "database-1-instance-1.ctkqko2k074a.us-east-2.rds.amazonaws.com";
$username = "admin";
$password = "Butterball3!";
$dbname = "projectDB";

try {
    // Create a PDO connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set PDO error mode to exception for better error handling
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected successfully"; // Optional message for successful connection (can be removed in production)
} catch (PDOException $e) {
    // Catch any connection errors and display a user-friendly message
    die("Connection failed: " . $e->getMessage());
}
?>
