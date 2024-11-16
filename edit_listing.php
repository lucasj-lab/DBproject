<?php
// Start session and check if user is logged in
session_start();
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
$error_message = "";

// Fetch listing details including state, city, and thumbnail
$stmt = $conn->prepare("
    SELECT Title, Description, Price, State, City, Thumbnail_Image 
    FROM listings 
    WHERE Listing_ID = ? AND User_ID = ?
");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$stmt->bind_result($title, $description, $price, $state, $city, $thumbnail_image);

if (!$stmt->fetch()) {
    $error_message = "Listing not found or you do not have permission to edit this listing.";
    $stmt->close();
    $conn->close();
    die($error_message);
}
$stmt->close();

// Handle form submission for updating the listing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $state = $_POST['state'] ?? '';
    $city = $_POST['city'] ?? '';

    $updateStmt = $conn->prepare("
        UPDATE listings 
        SET Title = ?, Description = ?, Price = ?, State = ?, City = ? 
        WHERE Listing_ID = ? AND User_ID = ?
    ");
    $updateStmt->bind_param("ssdssii", $title, $description, $price, $state, $city, $listing_id, $user_id);

    if ($updateStmt->execute()) {
        header("Location: user_dashboard.php");  // Redirect after successful update
        exit();
    } else {
        $error_message = "Error updating listing.";
    }
    $updateStmt->close();
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
</head>
<body>
    <?php include 'header.php'; ?>
    <header>
        <h1 class="edit-listing-title">Edit Listing</h1>
    </header>

    <div class="edit-listing-container">
        <!-- Thumbnail Section -->
        <form id="edit-thumbnail-form" method="POST" action="update_thumbnail.php">
            <h2 class="edit-listing-subtitle">Set Thumbnail</h2>
            <div class="form-group">
                <label class="form-label" for="thumbnail">Select Thumbnail:</label>
                <select name="thumbnail" id="thumbnail" required>
                    <option value="<?= htmlspecialchars($thumbnail_image); ?>">Current Thumbnail</option>
                    <?php foreach ($additionalImages as $image): ?>
                        <option value="<?= htmlspecialchars($image['Image_URL']); ?>">
                            <?= htmlspecialchars(basename($image['Image_URL'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="submit-button-container">
                <button type="submit">Update Thumbnail</button>
            </div>
        </form>

        <!-- Edit Listing Form -->
        <form id="edit-listing-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="listing_id" value="<?= htmlspecialchars($listing_id); ?>">

            <!-- Title -->
            <div class="form-group">
                <label class="form-label" for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($title); ?>" required>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label class="form-label" for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($description); ?></textarea>
            </div>

            <!-- Price -->
            <div class="form-group">
                <label class="form-label" for="price">Price:</label>
                <input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($price); ?>" required>
            </div>

            <!-- State -->
            <div class="form-group">
                <label class="form-label" for="state">State:</label>
                <select id="state" name="state" required>
                    <option value="AL" <?= $state === "AL" ? "selected" : ""; ?>>Alabama</option>
                    <option value="AK" <?= $state === "AK" ? "selected" : ""; ?>>Alaska</option>
                    <option value="AZ" <?= $state === "AZ" ? "selected" : ""; ?>>Arizona</option>
                    <option value="AR" <?= $state === "AR" ? "selected" : ""; ?>>Arkansas</option>
                    <option value="CA" <?= $state === "CA" ? "selected" : ""; ?>>California</option>
                    <option value="CO" <?= $state === "CO" ? "selected" : ""; ?>>Colorado</option>
                    <!-- Add other states as needed -->
                </select>
            </div>

            <!-- City -->
            <div class="form-group">
                <label class="form-label" for="city">City:</label>
                <input type="text" id="city" name="city" value="<?= htmlspecialchars($city); ?>" required>
            </div>

            <!-- File Upload -->
            <div class="file-upload-container">
                <label class="form-label" for="images">Upload New Images:</label>
                <input type="file" id="images" name="images[]" class="file-input" accept=".jpg, .jpeg, .png, .heic, .heif" multiple>
                <label for="images" class="file-upload-button">Choose Files</label>
                <span class="file-upload-text" id="file-upload-text">No files chosen</span>
            </div>

            <!-- Image Preview -->
            <div id="imagePreviewContainer"></div>

            <!-- Submit Button -->
            <div class="btn-container">
                <button type="submit">Update</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const imageInput = document.querySelector("#images");
            const previewContainer = document.getElementById("imagePreviewContainer");

            imageInput.addEventListener("change", function () {
                previewContainer.innerHTML = ""; // Clear previous previews
                Array.from(imageInput.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const img = document.createElement("img");
                        img.src = e.target.result;
                        img.classList.add("preview-image");
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            });
        });
    </script>

    <style>
        #imagePreviewContainer {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            overflow-x: auto;
        }

        .preview-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>

    <?php include 'footer.php'; ?>
</body>
</html>