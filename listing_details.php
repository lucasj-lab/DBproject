<?php

require 'database_connection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Function to fetch all listings
 * 
 * @param mysqli $conn The database connection object
 * @return array The array of listings
 * @throws Exception If there is a database or query error
 */
function getAllListings($conn)
{
    if (!$conn) {
        throw new Exception("Invalid database connection.");
    }

    // Define the SQL query
    $sql = "
    SELECT 
        listings.Listing_ID,
        listings.Title,
        listings.Description,
        listings.Price,
        listings.Date_Posted,
        listings.State,
        listings.City,
        category.Category_Name,
        user.Name AS User_Name,
        images.Image_URL AS Thumbnail_Image
    FROM listings
    LEFT JOIN category ON listings.Category_ID = category.Category_ID
    LEFT JOIN user ON listings.User_ID = user.User_ID
    LEFT JOIN images ON listings.Listing_ID = images.Listing_ID AND images.Is_Thumbnail = 1
    ORDER BY listings.Date_Posted DESC
    ";

    error_log("Executing SQL Query: $sql");

    // Prepare and execute the query
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Query execution failed: " . $conn->error);
    }

    // Fetch the data
    $listings = [];
    while ($row = $result->fetch_assoc()) {
        $listings[] = $row;
    }

    $result->free(); // Free the result set
    return $listings;
}

/**
 * Function to fetch details and images for a single listing
 * 
 * @param mysqli $conn The database connection object
 * @param int $listingID The ID of the listing
 * @return array The listing details and images
 * @throws Exception If there is a database or query error
 */
function getListingDetails($conn, $listingID)
{
    if (!$conn) {
        throw new Exception("Invalid database connection.");
    }

    // Fetch listing details
    $sql = "
    SELECT 
        listings.Listing_ID,
        listings.Title,
        listings.Description,
        listings.Price,
        listings.Date_Posted,
        listings.State,
        listings.City,
        category.Category_Name,
        user.Name AS User_Name
    FROM listings
    LEFT JOIN category ON listings.Category_ID = category.Category_ID
    LEFT JOIN user ON listings.User_ID = user.User_ID
    WHERE listings.Listing_ID = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $listingID);
    $stmt->execute();
    $result = $stmt->get_result();
    $listing = $result->fetch_assoc();
    $stmt->close();
    
    if (!$listing) {
        die("Listing not found.");
    }
    
    // Fetch images
    $images = [];
    $sql = "SELECT Image_URL FROM images WHERE Listing_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $listingID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $images[] = $row['Image_URL'];
    }
    $stmt->close();

    $listing['Images'] = $images;
    return $listing;
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Check if fetching all listings
        if (isset($_GET['fetchListings'])) {
            $listings = getAllListings($conn);

            if (empty($listings)) {
                error_log("No listings found");
                $response = ["message" => "No listings available."];
            } else {
                foreach ($listings as &$listing) {
                    $datePosted = $listing['Date_Posted'] ? new DateTime($listing['Date_Posted']) : null;
                    $listing['Formatted_Date'] = $datePosted ? $datePosted->format('F j, Y') : "Date not available";
                }
                $response = $listings;
            }

            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        // Check if fetching a single listing's details
        if (isset($_GET['listing_id'])) {
            $listingID = intval($_GET['listing_id']);
            $listingDetails = getListingDetails($conn, $listingID);

            header('Content-Type: application/json');
            echo json_encode($listingDetails);
            exit();
        }

        // If no valid parameters are provided
        header('Content-Type: application/json');
        echo json_encode(["error" => "Invalid request."]);
        exit();

    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(["error" => $e->getMessage()]);
    }
    exit();
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

        // Close Popup
        function closePopup() {
            const popup = document.querySelector('.popup-overlay');
            if (popup) popup.style.display = 'none';
        }
    </script>
</body>
</html>
