<?php
// Database connection parameters
$servername = "localhost"; // Use 'localhost' for local development
$username = "root";        // Default username for XAMPP MySQL
$password = "";            // Default password (blank for XAMPP's default setup)
$dbname = "projectDB";     // Replace with your actual database name

// Enable error reporting for development (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully via PDO!<br>";
} catch (PDOException $e) {
    die("Database connection (PDO) failed: " . $e->getMessage());
}

try {
    // Create a MySQLi connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check the MySQLi connection
    if ($conn->connect_error) {
        throw new Exception("Database connection (MySQLi) failed: " . $conn->connect_error);
    } else {
        echo "Connected successfully via MySQLi!<br>";
    }
} catch (Exception $e) {
    die($e->getMessage());
}
?>
