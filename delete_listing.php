<?php
session_start();
require 'database_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Verify the listing_id parameter
if (!isset($_GET['listing_id']) || !is_numeric($_GET['listing_id'])) {
    die("Invalid listing ID.");
}

$listing_id = intval($_GET['listing_id']);
$user_id = $_SESSION['user_id'];

try {
    // Prepare a delete query with a check to ensure the listing belongs to the logged-in user
    $stmt = $pdo->prepare("DELETE FROM listings WHERE Listing_ID = :listing_id AND User_ID = :user_id");
    $stmt->bindParam(':listing_id', $listing_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Redirect back to user dashboard with a success message
    header("Location: user_dashboard.php?message=Listing deleted successfully");
    exit();

} catch (PDOException $e) {
    echo "Error deleting listing: " . $e->getMessage();
}
