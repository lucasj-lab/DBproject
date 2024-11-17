<?php
session_start();
require 'database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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
    SELECT Image_ID, Image_URL, Is_Thumbnail 
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

    // Update listing details
    $updateStmt = $conn->prepare("
        UPDATE listings 
        SET Title = ?, Description = ?, Price = ?, State = ?, City = ? 
        WHERE Listing_ID = ? AND User_ID = ?
    ");
    $updateStmt->bind_param("ssdssii", $title, $description, $price, $state, $city, $listing_id, $user_id);
    $updateStmt->execute();
    $updateStmt->close();

    // Update thumbnail selection
    if ($selected_thumbnail) {
        $resetThumbnailStmt = $conn->prepare("UPDATE images SET Is_Thumbnail = 0 WHERE Listing_ID = ?");
        $resetThumbnailStmt->bind_param("i", $listing_id);
        $resetThumbnailStmt->execute();

        $setThumbnailStmt = $conn->prepare("UPDATE images SET Is_Thumbnail = 1 WHERE Image_ID = ? AND Listing_ID = ?");
        $setThumbnailStmt->bind_param("ii", $selected_thumbnail, $listing_id);
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

    // Redirect after successful update
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
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Styles for the image gallery */
        .image-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .image-gallery .image-item {
            position: relative;
            text-align: center;
        }

        .image-gallery img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .image-gallery input[type="radio"] {
            margin-top: 5px;
        }

        .image-gallery label {
            font-size: 0.8rem;
            display: block;
        }

        /* File upload section styling */
        .file-upload-container {
            margin-top: 20px;
            text-align: center;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const imageInput = document.getElementById("images");
            const galleryContainer = document.querySelector(".image-gallery");

            imageInput.addEventListener("change", function () {
                const files = Array.from(imageInput.files);

                // Remove previous previews
                const previewItems = galleryContainer.querySelectorAll(".preview-item");
                previewItems.forEach(item => item.remove());

                // Display new previews
                files.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const previewDiv = document.createElement("div");
                        previewDiv.classList.add("image-item", "preview-item"); // Mark as preview for removal on new upload

                        previewDiv.innerHTML = `
                            <img src="${e.target.result}" alt="New Image Preview">
                            <label>New Image</label>
                        `;

                        galleryContainer.appendChild(previewDiv);
                    };
                    reader.readAsDataURL(file);
                });
            });
        });
    </script>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="edit-listing-container">
        <h1 class="edit-listing-title">Edit Listing</h1>
        <form id="edit-listing-form" method="POST" enctype="multipart/form-data">
            <div class="listing-form-group">
                <input type="text" id="title" name="title" placeholder="Title" value="<?= htmlspecialchars($title); ?>" required>
                <textarea id="description" name="description" rows="4" placeholder="Description" required><?= htmlspecialchars($description); ?></textarea>
                <input type="number" step="0.01" id="price" name="price" placeholder="Price" value="<?= htmlspecialchars($price); ?>" required>

                <select id="state" name="state" onchange="updateCities()" required>
                    <option value="AL" <?= $state === "AL" ? "selected" : ""; ?>>Alabama</option>
                    <option value="AK" <?= $state === "AK" ? "selected" : ""; ?>>Alaska</option>
                    <option value="AZ" <?= $state === "AZ" ? "selected" : ""; ?>>Arizona</option>
                    <option value="AR" <?= $state === "AR" ? "selected" : ""; ?>>Arkansas</option>
                    <option value="CA" <?= $state === "CA" ? "selected" : ""; ?>>California</option>
                    <option value="CO" <?= $state === "CO" ? "selected" : ""; ?>>Colorado</option>
                </select>

                <div class="listing-city-group">
                    <select id="city-dropdown" name="city" required>
                        <option value="<?= htmlspecialchars($city); ?>" selected><?= htmlspecialchars($city); ?></option>
                    </select>
                </div>

                <!-- Combined Image Gallery -->
                <div class="image-gallery">
                    <!-- Existing Images -->
                    <?php foreach ($images as $image): ?>
                        <div class="image-item">
                            <img src="<?= htmlspecialchars($image['Image_URL']); ?>" alt="Listing Image">
                            <input type="radio" name="selected_thumbnail" value="<?= $image['Image_ID']; ?>" <?= $image['Is_Thumbnail'] ? "checked" : ""; ?>>
                            <label>Set as Thumbnail</label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Upload new images -->
                <div class="file-upload-container">
                    <input type="file" id="images" name="images[]" class="file-input" accept=".jpg, .jpeg, .png, .heic, .heif" multiple>
                    <label for="images" class="file-upload-button">Choose Files</label>
                </div>
            </div>

            <div class="btn-container">
                <button type="submit">Update</button>
            </div>
        </form>
    </div>
</body>

</html>
