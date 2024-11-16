<?php
require 'database_connection.php';
require 'listing_queries.php';

// Validate and get the listing ID from the URL
if (isset($_GET['listing_id']) && is_numeric($_GET['listing_id'])) {
    $listing_id = intval($_GET['listing_id']);
} else {
    die("Error: Invalid or missing listing ID.");
}

// Fetch the listing details
$listing = getListingDetails($pdo, $listing_id);

if (empty($listing)) {
    die("Error: Listing not found.");
}

$thumbnail = htmlspecialchars($listing['Thumbnail_Image']);
$additionalImages = $listing['Images'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listing Details</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="create-listing-container"> <!-- Main container for the listing -->
        <h1 class="edit-listing-title">Listing Details</h1>

        <!-- Image Gallery Section -->
        <div class="image-gallery">
            <img id="mainImage" src="<?= htmlspecialchars($listing['Thumbnail_Image']); ?>" class="main-image" alt="Main Image">
            <div class="thumbnail-container">
                <?php foreach ($listing['Images'] as $image): ?>
                    <img 
                        src="<?= htmlspecialchars($image); ?>" 
                        class="thumbnail-image" 
                        onclick="changeMainImage(this.src)" 
                        alt="Thumbnail"
                    >
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Listing Details Wrapper -->
        <div class="listing-details-wrapper">
            <div class="form-group">
                <label for="title"><strong>Title:</strong></label>
                <p id="title"><?= htmlspecialchars($listing['Title']); ?></p>
            </div>

            <div class="form-group">
                <label for="description"><strong>Description:</strong></label>
                <p id="description"><?= htmlspecialchars($listing['Description']); ?></p>
            </div>

            <div class="form-group">
                <label for="price"><strong>Price:</strong></label>
                <p id="price">$<?= htmlspecialchars(number_format($listing['Price'], 2)); ?></p>
            </div>

            <div class="form-group">
                <label for="state"><strong>State:</strong></label>
                <p id="state"><?= htmlspecialchars($listing['State']); ?></p>
            </div>

            <div class="form-group">
                <label for="city"><strong>City:</strong></label>
                <p id="city"><?= htmlspecialchars($listing['City']); ?></p>
            </div>
        </div>

        <!-- Back to Listings Button -->
        <div class="btn-container">
            <a href="listings.php" class="pill-button">Back to Listings</a>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Function to update the main image when a thumbnail is clicked
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;
        }
    </script>
</body>
</html>


