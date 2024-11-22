<?php
require 'database_connection.php';

$listingId = $_GET['listing_id'] ?? null;

// Check if listing ID is provided
if (!$listingId) {
    echo "Listing ID is missing.";
    exit;
}

try {
    // Prepare and execute query to fetch listing details
    $stmt = $pdo->prepare("
        SELECT 
            l.Listing_ID,
            l.Title,
            l.Description,
            l.Price,
            l.Date_Posted,
            l.State,
            l.City,
            u.Name AS User_Name, 
            c.Category_Name,
            GROUP_CONCAT(i.Image_URL) AS Images
        FROM listings l
        LEFT JOIN user u ON l.User_ID = u.User_ID
        LEFT JOIN category c ON l.Category_ID = c.Category_ID
        LEFT JOIN images i ON l.Listing_ID = i.Listing_ID
        WHERE l.Listing_ID = ?
        GROUP BY l.Listing_ID
    ");
    $stmt->execute([$listingId]);
    $listing = $stmt->fetch(PDO::FETCH_ASSOC);

    // Extract images into an array
    $images = $listing && $listing['Images'] ? explode(',', $listing['Images']) : [];
} catch (Exception $e) {
    echo "An error occurred while fetching listing details: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($listing['Title'] ?? 'Listing Details'); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="create-listing-container">
        <h1 class="edit-listing-title"><?php echo htmlspecialchars($listing['Title'] ?? 'Not Available'); ?></h1>
        <img id="mainImage" src="<?php echo htmlspecialchars($images[0] ?? 'uploads/default-image.jpg'); ?>" class="main-image" alt="Main Image">
        <!-- Image Gallery -->
        <div class="image-gallery">
            <div class="thumbnail-container">
                <?php foreach ($images as $image): ?>
                    <img src="<?php echo htmlspecialchars($image); ?>" class="thumbnail-image" onclick="changeMainImage(this.src)" alt="Thumbnail">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Listing Details -->
        <div class="listing-details-wrapper">
            <div class="form-group">
                <label for="title"><strong>Title:</strong></label>
                <p id="title"><?php echo htmlspecialchars($listing['Title'] ?? 'Not Available'); ?></p>
            </div>
            <div class="form-group">
                <label for="description"><strong>Description:</strong></label>
                <p id="description"><?php echo htmlspecialchars($listing['Description'] ?? 'Not Available'); ?></p>
            </div>
            <div class="form-group">
                <label for="price"><strong>Price:</strong></label>
                <p id="price">$<?php echo htmlspecialchars($listing['Price'] ?? 'Not Available'); ?></p>
            </div>
            <div class="form-group">
                <label for="date_posted"><strong>Date Posted:</strong></label>
                <p id="date_posted">
                    <?php echo $listing['Date_Posted'] 
                        ? htmlspecialchars(date("F j, Y", strtotime($listing['Date_Posted']))) 
                        : 'Not Available'; ?>
                </p>
            </div>
            <div class="form-group">
                <label for="category"><strong>Category:</strong></label>
                <p id="category"><?php echo htmlspecialchars($listing['Category_Name'] ?? 'Not Available'); ?></p>
            </div>
            <div class="form-group">
                <label for="user"><strong>Posted By:</strong></label>
                <p id="user"><?php echo htmlspecialchars($listing['User_Name'] ?? 'Not Available'); ?></p>
            </div>
            <div class="form-group">
                <label for="location"><strong>Location:</strong></label>
                <p id="location"><?php echo htmlspecialchars(($listing['City'] ?? 'Not Available') . ', ' . ($listing['State'] ?? 'Not Available')); ?></p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="text-align: center; margin-top: 20px;">
            <button id="buyNowBtn" class="btn">Buy Now</button>
            <a href="listings.php" class="btn">All Listings</a>
            <button onclick="history.back()" class="btn">Go Back</button>
        </div>
    </div>

    <!-- Modal -->
    <div id="buyNowModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">×</span>
            <h2>Buy Now</h2>
            <p><strong>Title:</strong> <?php echo htmlspecialchars($listing['Title'] ?? 'Not Available'); ?></p>
            <p><strong>Price:</strong> $<?php echo htmlspecialchars($listing['Price'] ?? 'Not Available'); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($listing['Description'] ?? 'Not Available'); ?></p>
            <form action="process_purchase.php" method="POST">
                <input type="hidden" name="listingId" value="<?php echo htmlspecialchars($listing['Listing_ID']); ?>">
                <button type="submit" class="btn">Confirm Purchase</button>
            </form>
        </div>
    </div>

    <script>
        // Change the main image when a thumbnail is clicked
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;
        }

        // Modal Logic
        const modal = document.getElementById('buyNowModal');
        const btn = document.getElementById('buyNowBtn');
        const close = document.getElementById('closeModal');

        // Open Modal
        btn.onclick = function () {
            modal.style.display = "flex";
        };

        // Close Modal
        close.onclick = function () {
            modal.style.display = "none";
        };

        // Close Modal when clicking outside
        window.onclick = function (event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };
    </script>
</body>
<footer>
    <?php include 'footer.php'; ?>
</footer>
</html>