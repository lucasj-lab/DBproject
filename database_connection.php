<?php
// Database connection settings
$servername = "127.0.0.1"; 
$port = "3307";            // Replace with your actual port if it's different
$username = "root";        
$password = "admin";       // Replace with the correct password or leave empty if none
$dbname = "projectDB";     

try {
    // Create a new PDO instance with DSN, including port
    $pdo = new PDO("mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8", $username, $password);

    // Set PDO attributes for error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to the database successfully!";
} catch (PDOException $e) {
    // Catch and display connection errors
    echo "Database connection failed: " . $e->getMessage();
}
