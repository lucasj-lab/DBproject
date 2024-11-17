<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'database_connection.php';

// Check if listing_id is provided in the URL
if (!isset($_GET['listing_id'])) {
    die("No listing ID provided.");
}

$listing_id = intval($_GET['listing_id']);
$user_id = $_SESSION['user_id'];

// Fetch listing details
$stmt = $conn->prepare("
    SELECT Title, Description, Price, State, City 
    FROM listings 
    WHERE Listing_ID = ? AND User_ID = ?
");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$stmt->bind_result($title, $description, $price, $state, $city);

if (!$stmt->fetch()) {
    die("Listing not found or you do not have permission to edit this listing.");
}
$stmt->close();

// Fetch images for the listing
$imageStmt = $conn->prepare("
    SELECT Image_URL, Is_Thumbnail 
    FROM images 
    WHERE Listing_ID = ?
");
$imageStmt->bind_param("i", $listing_id);
$imageStmt->execute();
$result = $imageStmt->get_result();
$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}
$imageStmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0.0;
    $state = $_POST['state'] ?? '';
    $city = $_POST['city'] ?? '';
    $selected_thumbnail = $_POST['selected_thumbnail'] ?? null;

    // Update the listing details
    $updateStmt = $conn->prepare("
        UPDATE listings 
        SET Title = ?, Description = ?, Price = ?, State = ?, City = ? 
        WHERE Listing_ID = ? AND User_ID = ?
    ");
    $updateStmt->bind_param("ssdssii", $title, $description, $price, $state, $city, $listing_id, $user_id);

    if ($updateStmt->execute()) {
        // Update thumbnail selection
        if ($selected_thumbnail) {
            $resetThumbnailStmt = $conn->prepare("UPDATE images SET Is_Thumbnail = 0 WHERE Listing_ID = ?");
            $resetThumbnailStmt->bind_param("i", $listing_id);
            $resetThumbnailStmt->execute();

            $setThumbnailStmt = $conn->prepare("UPDATE images SET Is_Thumbnail = 1 WHERE Image_URL = ? AND Listing_ID = ?");
            $setThumbnailStmt->bind_param("si", $selected_thumbnail, $listing_id);
            $setThumbnailStmt->execute();
        }

        // Handle new image uploads
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDirectory = 'uploads/';
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                $fileType = mime_content_type($tmpName);
                $allowedTypes = ['image/jpeg', 'image/png', 'image/heic', 'image/heif'];
                if (in_array($fileType, $allowedTypes)) {
                    $imageName = basename($_FILES['images']['name'][$key]);
                    $uniqueImageName = time() . "_" . $imageName;
                    $targetFilePath = $uploadDirectory . $uniqueImageName;

                    if (move_uploaded_file($tmpName, $targetFilePath)) {
                        $isThumbnail = ($key === 0 && empty($selected_thumbnail)) ? 1 : 0;
                        $imageUrl = $targetFilePath;

                        $imageStmt = $conn->prepare("INSERT INTO images (Listing_ID, Image_URL, Is_Thumbnail) VALUES (?, ?, ?)");
                        $imageStmt->bind_param("isi", $listing_id, $imageUrl, $isThumbnail);
                        $imageStmt->execute();
                    }
                }
            }
        }

        // Success message display
        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Listing Updated Successfully</title>
            <link rel='stylesheet' href='styles.css'>
        </head>
        <body>
            <div class='success-message-container'>
                <div class='success-message'>
                    <h2>Your listing has been successfully updated!</h2>
                    <p>You can take the following actions:</p>
                    <div class='navigation-options'>
                        <a href='edit_listing.php?listing_id=" . htmlspecialchars($listing_id) . "' class='pill-button'>Edit This Listing Again</a>
                        <a href='create_listing.php' class='pill-button'>Create Another Listing</a>
                        <a href='listings.php' class='pill-button'>Browse All Listings</a>
                        <a href='user_dashboard.php' class='pill-button'>Go to Dashboard</a>
                    </div>
                </div>
            </div>
        </body>
        </html>";
        exit();
    } else {
        echo "Error updating listing.";
    }
    $updateStmt->close();
}

$conn->close();
?>
