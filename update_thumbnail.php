<?php
session_start();
require 'database_connection.php'; // Include your database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $newThumbnail = $_POST['thumbnail'] ?? null;
    $listingId = $_POST['listing_id'] ?? null;

    if (!$newThumbnail || !$listingId) {
        header("Location: user_dashboard.php?msg=Invalid input.");
        exit();
    }

    try {
        // Prepare and execute the query to update the thumbnail
        $sql = "UPDATE listings SET Thumbnail_Image = :thumbnail WHERE Listing_ID = :listing_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['thumbnail' => $newThumbnail, 'listing_id' => $listingId]);

        // Redirect to user dashboard with a success message
        header("Location: user_dashboard.php?msg=Thumbnail updated successfully");
        exit();
    } catch (PDOException $e) {
        // Log the error and redirect with an error message
        error_log("Database error: " . $e->getMessage());
        header("Location: user_dashboard.php?msg=Failed to update thumbnail. Please try again.");
        exit();
    }
} else {
    // If not a POST request, redirect to the user dashboard
    header("Location: user_dashboard.php?msg=Invalid request.");
    exit();
}
?>
