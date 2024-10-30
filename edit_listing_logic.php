<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "database-1.c5qwuo6qo0y3.us-east-2.rds.amazonaws.com";
$username = "admin";
$password = "Imtheman198627*";
$dbname = "new_craigslist_db";

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$listing_id = $_GET['listing_id'];
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    $updateStmt = $conn->prepare("UPDATE listings SET Title = ?, Description = ?, Price = ? WHERE Listing_ID = ? AND User_ID = ?");
    $updateStmt->bind_param("ssdii", $title, $description, $price, $listing_id, $user_id);

    if ($updateStmt->execute()) {
        header("Location: account.php");
        exit();
    } else {
        $error_message = "Error updating listing.";
    }
    $updateStmt->close();
}

// Fetch listing details
$stmt = $conn->prepare("SELECT Title, Description, Price FROM listings WHERE Listing_ID = ? AND User_ID = ?");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$stmt->bind_result($title, $description, $price);
$stmt->fetch();

$stmt->close();
$conn->close();
?>
