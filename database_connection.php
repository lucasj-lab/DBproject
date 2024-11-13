<?php
// Database connection settings
$servername = "127.0.0.1"; 
$username = "root";        
$password = "admin";          
$dbname = "projectdb";     
$port = "3307";  //

try {
    // Create a new PDO instance with DSN (Data Source Name)
    $pdo = new PDO("mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8", $username, $password);

    // Set PDO attributes for error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to the database successfully!";
} catch (PDOException $e) {
    // Catch and display connection errors
    echo "Database connection failed: " . $e->getMessage();
}
?>