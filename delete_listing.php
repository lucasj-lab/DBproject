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
    exit(); // Ensure no further output after handling the request
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Listing</title>
</head>
<body>
    <button id="deleteListingButton">Delete Listing</button>

    <script>
        document.getElementById('deleteListingButton').addEventListener('click', function () {
            // Replace 123 with the actual listing ID
            const listingId = 123; 

            fetch('delete_listing.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ listing_id: listingId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // Optionally, redirect or reload the page after deletion
                        window.location.reload();
                    } else {
                        console.error(data.error);
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An unexpected error occurred. Please try again later.');
                });
        });
    </script>
</body>
</html>
