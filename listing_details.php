<?php
require 'database_connection.php';

$listingId = $_GET['listing_id'] ?? null;

if ($listingId) {
    $stmt = $pdo->prepare("
        SELECT 
            l.*, 
            GROUP_CONCAT(i.Image_URL) AS Images 
        FROM listings l
        LEFT JOIN images i ON l.Listing_ID = i.Listing_ID
        WHERE l.Listing_ID = ?
        GROUP BY l.Listing_ID
    ");
    $stmt->execute([$listingId]);
    $listing = $stmt->fetch(PDO::FETCH_ASSOC);
    $images = $listing && $listing['Images'] ? explode(',', $listing['Images']) : [];
} else {
    echo "Listing ID is missing.";
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
        <h1 class="edit-listing-title"><?php echo htmlspecialchars($listing['Title']); ?></h1>

        <!-- Image Gallery -->
        <div class="image-gallery">
            <img id="mainImage" src="<?php echo htmlspecialchars($images[0] ?? 'uploads/default-image.jpg'); ?>" class="main-image" alt="Main Image">
            <div class="thumbnail-container">
                <?php foreach ($images as $image): ?>
                    <img src="<?php echo htmlspecialchars($image); ?>" class="thumbnail-image" onclick="changeMainImage(this.src)" alt="Thumbnail">
                <?php endforeach; ?>
            </div>
        </div>

        <div class="listing-details-wrapper">
    <div class="form-group">
        <label for="title"><strong>Title:</strong></label>
        <p id="title"><?php echo htmlspecialchars($listing['Title']); ?></p>
    </div>
    <div class="form-group">
        <label for="description"><strong>Description:</strong></label>
        <p id="description"><?php echo htmlspecialchars($listing['Description']); ?></p>
    </div>
    <div class="form-group">
        <label for="price"><strong>Price:</strong></label>
        <p id="price">$<?php echo htmlspecialchars($listing['Price']); ?></p>
    </div>
    <div class="form-group">
        <label for="date_posted"><strong>Date Posted:</strong></label>
        <p id="date_posted"><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($listing['Date_Posted']))); ?></p>
    </div>
    <div class="form-group">
        <label for="category"><strong>Category:</strong></label>
        <p id="category"><?php echo htmlspecialchars($listing['Category_Name']); ?></p>
    </div>
    <div class="form-group">
        <label for="user"><strong>Posted By:</strong></label>
        <p id="user"><?php echo htmlspecialchars($listing['User_Name']); ?></p>
    </div>
    <div class="form-group">
        <label for="location"><strong>Location:</strong></label>
        <p id="location"><?php echo htmlspecialchars($listing['City'] . ', ' . $listing['State']); ?></p>
    </div>
    <div class="form-group">
        <label for="condition"><strong>Condition:</strong></label>
        <p id="condition"><?php echo htmlspecialchars($listing['Condition']); ?></p>
    </div>
    <div class="form-group">
        <label for="shipping"><strong>Shipping Options:</strong></label>
        <p id="shipping"><?php echo htmlspecialchars($listing['Shipping']); ?></p>
    </div>
</div>

        <!-- Action Buttons -->
        <div style="text-align: center; margin-top: 20px;">
            <a href="listings.php" class="btn">All Listings</a>
            <button onclick="history.back()" class="btn">Go Back</button>
        </div>

        <!-- Messaging Section -->
        <div id="messagesContainer"></div>
        <form id="sendMessageForm">
            <input type="hidden" name="sender_id" value="1">
            <input type="hidden" name="receiver_id" value="2">
            <input type="hidden" name="listing_id" value="123">
            <textarea name="message_text" required></textarea>
            <button type="submit">Send</button>
        </form>
    </div>

    <!-- Modal -->
    <div id="buyNowModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">×</span>
            <h2>Buy Now</h2>
            <p><strong>Title:</strong> Amazing Product</p>
            <p><strong>Price:</strong> $100</p>
            <p><strong>Description:</strong> This is a fantastic product you will love!</p>
            <form action="process_purchase.php" method="POST">
                <input type="hidden" name="listingId" value="12345">
                <button type="submit" class="btn">Confirm Purchase</button>
            </form>
        </div>
    </div>

    <script>
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;

            // Handle Modal Logic
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

// Close Modal on Click Outside
window.onclick = function (event) {
    if (event.target === modal) {
        modal.style.display = "none";
    }
};

// Handle Messaging
document.getElementById('sendMessageForm').addEventListener('submit', function (e) {
    e.preventDefault();
    sendMessage('sendMessageForm');
});

// Fetch and Send Messages (Ensure fetchMessages and sendMessage are defined in messaging.js)
fetchMessages(1, 2, 123);

        }
    </script>
</body>
<footer>
    <?php include 'footer.php'; ?>
</footer>
</html>
