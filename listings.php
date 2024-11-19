<?php
require 'database_connection.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Validate and get the listing ID from the URL
if (isset($_GET['listing_id']) && is_numeric($_GET['listing_id'])) {
    $listing_id = intval($_GET['listing_id']);
} else {
    die("Error: Invalid or missing listing ID.");
}

// Fetch the listing details, including the thumbnail and additional images
$sql = "
    SELECT 
        l.Listing_ID, 
        l.Title, 
        l.Description, 
        l.Price, 
        l.State, 
        l.City, 
        COALESCE(i.Image_URL, 'no_image.png') AS Thumbnail_Image
    FROM 
        listings l
    LEFT JOIN 
        images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE 
        l.Listing_ID = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $listing_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

// Check if the listing exists
if (!$listing) {
    die("Error: Listing not found.");
}

// Fetch additional images
$images_sql = "
    SELECT Image_URL 
    FROM images 
    WHERE Listing_ID = ?
";
$images_stmt = $conn->prepare($images_sql);
$images_stmt->bind_param("i", $listing_id);
$images_stmt->execute();
$additionalImages = $images_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$images_stmt->close();
$conn->close();
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

<div class="create-listing-container">
    <h1 class="edit-listing-title">Listing Details</h1>

    <!-- Image Gallery -->
    <div class="image-gallery">
        <img id="mainImage" src="<?= htmlspecialchars($listing['Thumbnail_Image']); ?>" class="main-image" alt="Main Image">
        <div class="thumbnail-container">
            <?php foreach ($additionalImages as $image): ?>
                <img src="<?= htmlspecialchars($image['Image_URL']); ?>" class="thumbnail-image" onclick="changeMainImage(this.src)" alt="Thumbnail">
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Listing Details -->
    <div class="listing-details-wrapper">
        <div class="form-group">
            <label><strong>Title:</strong></label>
            <p><?= htmlspecialchars($listing['Title']); ?></p>
        </div>

        <div class="form-group">
            <label><strong>Description:</strong></label>
            <p><?= htmlspecialchars($listing['Description']); ?></p>
        </div>

        <div class="form-group">
            <label><strong>Price:</strong></label>
            <p>$<?= htmlspecialchars(number_format($listing['Price'], 2)); ?></p>
        </div>

        <div class="form-group">
            <label><strong>State:</strong></label>
            <p><?= htmlspecialchars($listing['State']); ?></p>
        </div>

        <div class="form-group">
            <label><strong>City:</strong></label>
            <p><?= htmlspecialchars($listing['City']); ?></p>
        </div>
    </div>

    <!-- Navigation and Buy Now -->
    <div style="text-align: center; margin-top: 20px;">
        <div style="display: flex; justify-content: space-around; margin-bottom: 10px;">
            <a href="browse_categories.php?category=<?= urlencode($listing['Category_Name'] ?? ''); ?>" class="btn">Return to Category</a>
            <a href="all_listings.php" class="btn">All Listings</a>
            <a href="profile.php?id=<?= htmlspecialchars($listing['User_ID'] ?? ''); ?>" class="btn">View Profile</a>
        </div>
        <a href="buy_now.php?item=<?= htmlspecialchars($listing_id); ?>" class="btn btn-large">BUY NOW</a>
    </div>
</div>

<script>
    function changeMainImage(src) {
        document.getElementById('mainImage').src = src;
    }
</script>
</body>
</html>
