<?php
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection parameters
$servername = "database-1-instance-1.cpgoq8m2kfkd.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "Bagflea3!";
$dbname = "CraigslistDB";

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if listing_id is provided in the URL
if (!isset($_GET['listing_id'])) {
    die("No listing ID provided.");
}

$listing_id = intval($_GET['listing_id']);
$user_id = $_SESSION['user_id'];
$error_message = "";

// Initialize variables to avoid undefined errors
$title = $description = "";
$price = 0.0;

// Handle form submission for updating the listing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;

    $updateStmt = $conn->prepare("UPDATE listings SET Title = ?, Description = ?, Price = ? WHERE Listing_ID = ? AND User_ID = ?");
    $updateStmt->bind_param("ssdii", $title, $description, $price, $listing_id, $user_id);

    if ($updateStmt->execute()) {
        header("Location: account.php");  // Redirect after successful update
        exit();
    } else {
        $error_message = "Error updating listing.";
    }
    $updateStmt->close();
}

// Fetch listing details if not a POST request
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $stmt = $conn->prepare("SELECT Title, Description, Price FROM listings WHERE Listing_ID = ? AND User_ID = ?");
    $stmt->bind_param("ii", $listing_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($title, $description, $price);

    // Check if a result was returned
    if (!$stmt->fetch()) {
        $error_message = "Listing not found or you do not have permission to edit this listing.";
    }

    $stmt->close();
}

$conn->close();
?>
