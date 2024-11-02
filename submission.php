<?php
session_start(); // Start session to access user information

require 'database_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to create a listing.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']); // Cast to float
    $state = trim($_POST['state']);
    $city = trim($_POST['city']) ?: trim($_POST['city-input']);

    // Validation for empty fields
    if (empty($title) || empty($description) || empty($price) || empty($state) || empty($city)) {
        echo "All fields are required.";
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO listings (User_ID, Title, Description, Price, State, City) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $description, $price, $state, $city]);
        echo "Success! Your listing has been created.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
