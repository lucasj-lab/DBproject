<?php 
require 'database_connection.php'; // Ensure this initializes $conn (MySQLi connection)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate the input
    $listingId = intval($_POST['listing_id'] ?? 0);

    if ($listingId > 0) {
        // Check if the listing exists
        $checkQuery = "SELECT * FROM listings WHERE Listing_ID = ?";
        $checkStmt = $conn->prepare($checkQuery);
        if (!$checkStmt) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
            exit();
        }
        $checkStmt->bind_param("i", $listingId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $listing = $result->fetch_assoc();
        $checkStmt->close();

        if (!$listing) {
            echo json_encode(['success' => false, 'error' => 'Listing not found.']);
            exit();
        }

        // Delete the listing
        $deleteQuery = "DELETE FROM listings WHERE Listing_ID = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        if (!$deleteStmt) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
            exit();
        }
        $deleteStmt->bind_param("i", $listingId);

        if ($deleteStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Listing deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete the listing.']);
        }
        $deleteStmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid listing ID.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>
