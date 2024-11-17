<?php
session_start();
require 'database_connection.php';

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
        // Start a transaction
        $pdo->beginTransaction();

        // Reset all thumbnails for the given listing
        $resetSql = "UPDATE images SET Is_Thumbnail = 0 WHERE Listing_ID = :listing_id";
        $resetStmt = $pdo->prepare($resetSql);
        $resetStmt->execute(['listing_id' => $listingId]);

        // Set the new thumbnail
        $updateSql = "UPDATE images SET Is_Thumbnail = 1 WHERE Image_URL = :image_url AND Listing_ID = :listing_id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute(['image_url' => $newThumbnail, 'listing_id' => $listingId]);

        // Optionally update the listings table for a quick reference to the thumbnail
        $listingsSql = "UPDATE listings SET Thumbnail_Image = :thumbnail WHERE Listing_ID = :listing_id";
        $listingsStmt = $pdo->prepare($listingsSql);
        $listingsStmt->execute(['thumbnail' => $newThumbnail, 'listing_id' => $listingId]);

        // Commit the transaction
        $pdo->commit();

        // Redirect with a success message
        header("Location: user_dashboard.php?msg=Thumbnail updated successfully");
        exit();
    } catch (PDOException $e) {
        // Rollback transaction on failure
        $pdo->rollBack();
        error_log("Database error: " . $e->getMessage());
        header("Location: user_dashboard.php?msg=Failed to update thumbnail. Please try again.");
        exit();
    }
} else {
    // If not a POST request, redirect to the user dashboard
    header("Location: user_dashboard.php?msg=Invalid request.");
    exit();
}
