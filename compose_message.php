<?php
require 'database_connection.php';
include 'header.php';

// Get the listing and recipient details from the URL
$listingID = intval($_GET['listing_id'] ?? 0);
$recipientID = intval($_GET['recipient_id'] ?? 0);

// Fetch listing and recipient details for display
$listing = [];
$recipient = [];
$images = [];

// Fetch listing details
if ($listingID) {
    $sql = "SELECT Title FROM listings WHERE Listing_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $listingID);
    $stmt->execute();
    $result = $stmt->get_result();
    $listing = $result->fetch_assoc();
    $stmt->close();

    // Fetch listing images
    $imagesQuery = "SELECT Image_URL FROM images WHERE Listing_ID = ?";
    $imgStmt = $conn->prepare($imagesQuery);
    $imgStmt->bind_param("i", $listingID);
    $imgStmt->execute();
    $imgResult = $imgStmt->get_result();
    while ($row = $imgResult->fetch_assoc()) {
        $images[] = $row['Image_URL'];
    }
    $imgStmt->close();
}

// Fetch recipient details
if ($recipientID) {
    $sql = "SELECT Name FROM user WHERE User_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $recipientID);
    $stmt->execute();
    $result = $stmt->get_result();
    $recipient = $result->fetch_assoc();
    $stmt->close();
}

// Handle missing data
if (!$listingID || !$recipientID || !$listing || !$recipient) {
    die("Invalid listing or recipient details provided.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compose Message</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Add the provided gallery and main image styling */
        img.thumbnail-option {
            width: -webkit-fill-available;
        }

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

        @media (max-width: 300px) {
            .main-image {
                width: 100%;
                max-width: 100%;
                height: auto;
            }
        }

        .image-gallery {
            display: flex;
            overflow-x: auto;
            overflow-y: hidden;
            gap: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            max-width: 100%;
            box-sizing: border-box;
            white-space: nowrap;
            justify-content: flex-start;
            align-items: center;
        }

        .image-gallery img {
            height: 100px;
            width: auto;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
        }

        .image-gallery img:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <div class="compose-message-container">
        <h1 class="page-title">Send a Message</h1>

        <p><strong>Listing:</strong> <?php echo htmlspecialchars($listing['Title'] ?? 'Unknown Listing'); ?></p>
        <p><strong>To:</strong> <?php echo htmlspecialchars($recipient['Name'] ?? 'Unknown Recipient'); ?></p>

        <!-- Image Gallery -->
        <?php if (!empty($images)): ?>
            <div class="main-image-container">
                <img id="mainImage" src="<?php echo htmlspecialchars($images[0]); ?>" alt="Main Listing Image" class="main-image">
            </div>
            <div class="image-gallery">
                <?php foreach ($images as $imageURL): ?>
                    <img src="<?php echo htmlspecialchars($imageURL); ?>" alt="Listing Image" class="thumbnail-option" onclick="updateMainImage('<?php echo htmlspecialchars($imageURL); ?>')">
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No images available for this listing.</p>
        <?php endif; ?>

        <!-- Compose Message Form -->
        <form action="send_message.php" method="POST" class="compose-message-form">
            <input type="hidden" name="listing_id" value="<?php echo $listingID; ?>">
            <input type="hidden" name="recipient_id" value="<?php echo $recipientID; ?>">

            <div class="form-group">
                <label for="message_text" class="form-label">Message:</label>
                <textarea name="message_text" id="message_text" class="message-textarea" rows="5" placeholder="Type your message here..." required></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn send-message-btn">Send Message</button>
            </div>
        </form>
    </div>

    <script>
        // JavaScript for updating the main image when a thumbnail is clicked
        function updateMainImage(imageURL) {
            const mainImage = document.getElementById('mainImage');
            mainImage.src = imageURL;
        }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
