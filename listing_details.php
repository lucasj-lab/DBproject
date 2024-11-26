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

// Fetch the listing details and images
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
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
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
    <style>
        /* Main Thumbnail Section */
        .main-image-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .main-image {
            width: 100%;
            max-width: 600px;
            height: auto;
            object-fit: cover;
            border-radius: 5px;
            display: block;
            margin: 0 auto;
        }

        /* Image Gallery */
        .image-gallery {
            display: flex;
            overflow-x: auto;
            gap: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .thumbnail-image {
            height: 100px;
            width: auto;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .thumbnail-image:hover {
            transform: scale(1.1);
            border-color: lightgray;
        }
    </style>
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
                <p id="price">
                    <?php 
                        if (isset($listing['Price'])) {
                            $price = (float)$listing['Price'];
                            echo $price === 0.0 ? 'Free' : '$' . number_format($price, 2);
                        } else {
                            echo 'Not Available';
                        }
                    ?>
                </p>
            </div>
            <div class="form-group">
                <label for="category"><strong>Category:</strong></label>
                <p id="category"><?php echo htmlspecialchars($listing['Category_Name'] ?? 'Not Available'); ?></p>
            </div>
            <div class="form-group">
                <label for="location"><strong>Location:</strong></label>
                <p id="location"><?php echo htmlspecialchars(($listing['City'] ?? 'Not Available') . ', ' . ($listing['State'] ?? 'Not Available')); ?></p>
            </div>
            <div class="form-group">
                <label for="user"><strong>Posted:</strong></label>
                <p id="user"><?php echo htmlspecialchars($listing['User_Name'] ?? 'Not Available'); ?></p>
            </div>
            <div class="form-group">
                <label for="date_posted"><strong>Added:</strong></label>
                <p id="date_posted">
                    <?php echo $listing['Date_Posted'] 
                        ? htmlspecialchars(date("F j, Y", strtotime($listing['Date_Posted']))) 
                        : 'Not Available'; ?>
                </p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="text-align: center; margin-top: 20px;">
            <button id="buyNowBtn" class="btn">Buy Now</button>
            <button onclick="location.href='listings.php';" class="btn">All Listings</button>
            <button onclick="history.back()" class="btn">Go Back</button>
            <button onclick="location.href='compose_message.php?listing_id=<?php echo $listingID; ?>&recipient_id=<?php echo $recipientID; ?>'" class="btn">Message Owner</button>
        </div>
    </div>

    <!-- Popup for Success/Error Messages -->
    <?php if (isset($successMessage) || isset($errorMessage)): ?>
        <div class="popup-overlay">
            <div class="popup-container">
                <div class="popup-header">
                    <h1 class="popup-title">
                        <?php echo isset($successMessage) ? "Thank You!" : "Error"; ?>
                    </h1>
                </div>
                <div class="popup-body">
                    <p class="popup-message">
                        <?php echo isset($successMessage) ? htmlspecialchars($successMessage) : htmlspecialchars($errorMessage); ?>
                    </p>
                </div>
                <div class="popup-footer">
                    <button class="close-popup" onclick="closePopup()">Close</button>
                </div>
            </div>
        </div>
    <?php endif; ?>

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

        // Close Popup
        function closePopup() {
            const popup = document.querySelector('.popup-overlay');
            if (popup) popup.style.display = 'none';
        }
    </script>
</body>
</html>
