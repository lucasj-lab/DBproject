<?php
$servername = "database-1-instance-1.ctkqko2k074a.us-east-2.rds.amazonaws.com";
$username = "admin";
$password = "Butterball3!";
$dbname = "projectDB";

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set PDO error mode to exception for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Catch any connection errors and display a user-friendly message
    die("Database connection failed: " . $e->getMessage());
}

