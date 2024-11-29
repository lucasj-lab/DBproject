<?php
require 'database_connection.php';

// Handle form submission (Buy Now button)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listingId = intval($_POST['listingId'] ?? 0);

    if ($listingId) {
        $successMessage = "Thank you! Your purchase was successful for Listing ID: $listingId.";
    } else {
        $errorMessage = "An error occurred. Invalid listing.";
    }
}

// Fetch listing details
$listingId = intval($_GET['listing_id'] ?? 0);

if (!$listingId) {
    echo "Listing ID is missing.";
    exit;
}

// Fetch the listing details and all associated images
$query = "
    SELECT 
        l.Listing_ID,
        l.Title,
        l.Description,
        l.Price,
        l.Date_Posted,
        l.State,
        l.City,
        l.User_ID,
        u.Name AS User_Name,
        c.Category_Name,
        GROUP_CONCAT(i.Image_URL) AS Images
    FROM listings l
    LEFT JOIN user u ON l.User_ID = u.User_ID
    LEFT JOIN category c ON l.Category_ID = c.Category_ID
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID
    WHERE l.Listing_ID = ?
    GROUP BY l.Listing_ID
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $listingId);
$stmt->execute();
$result = $stmt->get_result();
$listing = $result->fetch_assoc();

$images = $listing && $listing['Images'] ? explode(',', $listing['Images']) : [];

if (!$listing) {
    echo "Listing not found.";
    exit;
}

// Get the owner ID for messaging
$listingID = $listing['Listing_ID'] ?? null;
$recipientID = $listing['User_ID'] ?? null;
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
        <div class="main-image-container">
            <img id="mainImage" src="<?php echo htmlspecialchars($images[0] ?? 'uploads/default-image.jpg'); ?>" class="main-image" alt="Main Image">
        </div>

        <!-- Image Gallery -->
        <div class="image-gallery">
            <?php foreach ($images as $image): ?>
                <img src="<?php echo htmlspecialchars($image); ?>" class="thumbnail-image" onclick="changeMainImage(this.src)" alt="Thumbnail">
            <?php endforeach; ?>
        </div>

        <!-- Listing Details -->
        <div class="listing-details-wrapper">
            <table border="1" style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Title</strong></td>
                        <td><?php echo htmlspecialchars($listing['Title'] ?? 'Not Available'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Description</strong></td>
                        <td><?php echo nl2br(htmlspecialchars($listing['Description'] ?? 'Not Available')); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Price</strong></td>
                        <td>$<?php echo htmlspecialchars($listing['Price'] ?? 'Not Available'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Category</strong></td>
                        <td><?php echo htmlspecialchars($listing['Category_Name'] ?? 'Not Available'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Location</strong></td>
                        <td><?php echo htmlspecialchars($listing['City'] . ', ' . $listing['State'] ?? 'Not Available'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Date Posted</strong></td>
                        <td><?php echo htmlspecialchars($listing['Date_Posted'] ?? 'Not Available'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Seller</strong></td>
                        <td><?php echo htmlspecialchars($listing['User_Name'] ?? 'Not Available'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Action Buttons -->
        <div style="text-align: center; margin-top: 20px;">
            <button id="buyNowBtn" class="btn">Buy Now</button>
            <button onclick="location.href='listings.php';" class="btn">All Listings</button>
            <button onclick="history.back()" class="btn">Go Back</button>
            <button onclick="location.href='compose_message.php?listing_id=<?php echo $listingID; ?>&recipient_id=<?php echo $recipientID; ?>'" class="btn">Message Owner</button>
        </div>
    </div>

    <!-- Buy Now Modal -->
    <div id="buyNowModal" class="modal">
        <div class="modal-content popup-container">
            <span class="close" id="closeModal">×</span>
            <h2>Buy Now</h2>
            <p><strong>Title:</strong> <?php echo htmlspecialchars($listing['Title'] ?? 'Not Available'); ?></p>
            <p><strong>Price:</strong> $<?php echo htmlspecialchars($listing['Price'] ?? 'Not Available'); ?></p>
            <form method="POST">
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
</html>
