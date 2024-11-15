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
    <title><?php echo htmlspecialchars($listing['Title']); ?></title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <div class="listing-details-container">
            <h1><?php echo htmlspecialchars($listing['Title']); ?></h1>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($listing['Description']); ?></p>
            <p><strong>Price:</strong> $<?php echo htmlspecialchars($listing['Price']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($listing['City'] . ', ' . $listing['State']); ?></p>
            <p><strong>Date Posted:</strong>
                <?php
                $datePosted = new DateTime($listing['Date_Posted']);
                echo htmlspecialchars($datePosted->format('l, F jS, Y'));
                ?>
            </p>

            <!-- Image Gallery -->
            <div class="image-gallery">
                <!-- Main Thumbnail -->
                <img id="mainImage" src="<?php echo $thumbnail; ?>" class="main-image" alt="Main Image">
                
                <?php if (!empty($additionalImages)): ?>
                    <!-- Scrollable Gallery -->
                    <div class="thumbnail-scroll-container">
                        <?php foreach ($additionalImages as $image): ?>
                            <img src="<?php echo htmlspecialchars($image); ?>" class="thumbnail-scroll-image" 
                                 onclick="changeMainImage(this.src)" alt="Additional Image">
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No additional images available for this listing.</p>
                <?php endif; ?>
            </div>

            <!-- Back to Listings button -->
            <a href="listings.php" class="pill-button back-to-listings">Back to Listings</a>
        </div>
    </main>

    <script>
        function changeMainImage(src) {
            document.getElementById("mainImage").src = src;
        }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
