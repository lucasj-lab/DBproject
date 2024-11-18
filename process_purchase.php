<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listingId = $_POST['listingId'] ?? null;

    if ($listingId) {
        // Add purchase processing logic here
        // Example: Save purchase details to the database or initiate payment

        echo "Thank you for purchasing listing ID: " . htmlspecialchars($listingId);
    } else {
        echo "Invalid listing. Please try again.";
    }
}
?>
