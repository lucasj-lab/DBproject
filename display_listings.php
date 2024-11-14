<?php
require 'database_connection.php';

// Fetch listings and associated images
$listingsSql = "SELECT listings.*, images.id AS image_id, images.image_url
                FROM listings
                LEFT JOIN images ON listings.id = images.listing_id
                ORDER BY listings.Date_Posted DESC";
$stmt = $conn->prepare($listingsSql);
$stmt->execute();
$result = $stmt->get_result();

// Display each listing with its images
while ($listing = $result->fetch_assoc()) {
    echo '<div class="listing-item">';
    echo '<h3>' . htmlspecialchars($listing['Title']) . '</h3>';
    echo '<p>Description: ' . htmlspecialchars($listing['Description']) . '</p>';
    echo '<p>Price: $' . htmlspecialchars($listing['Price']) . '</p>';
    echo '<p>Location: ' . htmlspecialchars($listing['City']) . ', ' . htmlspecialchars($listing['State']) . '</p>';

    // Display images if available
    if ($listing['image_url']) {
        echo '<div class="listing-images">';
        echo '<img id="image_' . htmlspecialchars($listing['image_id']) . '" src="' . htmlspecialchars($listing['image_url']) . '" alt="Listing Image" class="listing-image">';
        echo '</div>';
    } else {
        echo '<p>No image available</p>';
    }

    echo '</div>';
}
$stmt->close();
$conn->close();
?>
