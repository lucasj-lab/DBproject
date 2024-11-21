<?php
require 'database_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listingId = filter_input(INPUT_GET, 'listing_id', FILTER_VALIDATE_INT);

    if ($listingId) {
        try {
            // Check if the listing exists
            $checkStmt = $pdo->prepare("SELECT * FROM listings WHERE Listing_ID = :listing_id");
            $checkStmt->execute(['listing_id' => $listingId]);
            $listing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$listing) {
                echo json_encode(['success' => false, 'error' => 'Listing not found.']);
                exit();
            }

            // Delete the listing (cascading will handle related data)
            $stmt = $pdo->prepare("DELETE FROM listings WHERE Listing_ID = :listing_id");
            $stmt->execute(['listing_id' => $listingId]);

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            error_log("Error deleting listing: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Database error occurred.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid listing ID.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>
