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
        i.Image_URL AS Thumbnail_Image
    FROM 
        listings l
    LEFT JOIN 
        images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE 
        l.Listing_ID = :listing_id
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['listing_id' => $listing_id]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the listing exists
if (!$listing) {
    die("Error: Listing not found.");
}

// Fetch additional images
$images_sql = "
    SELECT Image_URL 
    FROM images 
    WHERE Listing_ID = :listing_id
";
$images_stmt = $pdo->prepare($images_sql);
$images_stmt->execute(['listing_id' => $listing_id]);
$additionalImages = $images_stmt->fetchAll(PDO::FETCH_COLUMN);

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
            <img id="mainImage" src="<?= htmlspecialchars($listing['Thumbnail_Image']); ?>" class="main-image"
                alt="Main Image">
            <div class="thumbnail-container">
                <?php foreach ($additionalImages as $image): ?>
                    <img src="<?= htmlspecialchars($image); ?>" class="thumbnail-image" onclick="changeMainImage(this.src)"
                        alt="Thumbnail">
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
        <div style="text-align: center; margin-top: 20px;">
    <!-- Top Row Links -->
    <div style="display: flex; justify-content: space-around; margin-bottom: 10px;">
        <a href="browse_category.php?category=<?php echo urlencode($category); ?>" class="btn">Return to Category</a>
        <a href="listings.php" class="btn">All Listings</a>
        <a href="user_profile.php?id=<?php echo htmlspecialchars($userId); ?>">View Profile</a>

    </div>
    
    
</div>


   
    <script>
        // Function to update the main image when a thumbnail is clicked
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;
        }
    </script>
</body>

</html>

<?php
// Sample listing details (replace with dynamic data from your database)
$listingTitle = "Amazing Product";
$listingPrice = "$100";
$listingDescription = "This is a fantastic product you will love!";
$listingId = 12345; // Replace with dynamic listing ID
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Now Modal</title>
    <style>
        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5); /* Black with opacity */
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            text-align: center;
        }

        .modal-content h2 {
            margin-top: 0;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }

        .btn {
            padding: 10px 20px;
            text-decoration: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            display: inline-block;
            margin-top: 15px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <!-- Buy Now Button -->
    <button id="buyNowBtn" class="btn">Buy Now</button>

    <!-- Modal -->
    <div id="buyNowModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2>Buy Now</h2>
            <p><strong>Title:</strong> <?php echo htmlspecialchars($listingTitle); ?></p>
            <p><strong>Price:</strong> <?php echo htmlspecialchars($listingPrice); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($listingDescription); ?></p>
            <form action="process_purchase.php" method="POST">
                <input type="hidden" name="listingId" value="<?php echo htmlspecialchars($listingId); ?>">
                <button type="submit" class="btn">Confirm Purchase</button>
            </form>
        </div>
    </div>

    <script>
        // JavaScript to Handle Modal
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

        // Close Modal when clicking outside the modal content
        window.onclick = function (event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };
    </script>
</body>

<?php include 'footer.php'; ?>

</html>
