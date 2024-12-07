<?php
$servername = "Joshuas-PC";
$username = "root";
$password = "";
$dbname = "projectDB";

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set PDO error mode to exception for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection (PDO) failed: " . $e->getMessage());
}

// Create a MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the MySQLi connection
if ($conn->connect_error) {
    die("Database connection (MySQLi) failed: " . $conn->connect_error);
}
?>
