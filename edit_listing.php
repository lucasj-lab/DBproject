<?php
session_start();

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

// Initialize variables
$title = $description = $state = $city = $thumbnail_image = "";
$price = 0.0;
$additionalImages = [];

// Fetch listing details for pre-filling the form
$stmt = $conn->prepare("
    SELECT Title, Description, Price, State, City, Thumbnail_Image 
    FROM listings 
    WHERE Listing_ID = ? AND User_ID = ?
");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$stmt->bind_result($title, $description, $price, $state, $city, $thumbnail_image);

if (!$stmt->fetch()) {
    die("Listing not found or you do not have permission to edit this listing.");
}
$stmt->close();

// Fetch additional images for the listing
$imageStmt = $conn->prepare("
    SELECT Image_URL 
    FROM images 
    WHERE Listing_ID = ?
");
$imageStmt->bind_param("i", $listing_id);
$imageStmt->execute();
$result = $imageStmt->get_result();
while ($row = $result->fetch_assoc()) {
    $additionalImages[] = $row['Image_URL'];
}
$imageStmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle listing update
    $title = $_POST['title'] ?? $title;
    $description = $_POST['description'] ?? $description;
    $price = $_POST['price'] ?? $price;
    $state = $_POST['state'] ?? $state;
    $city = $_POST['city'] ?? $city;
    $newThumbnail = $_POST['thumbnail'] ?? $thumbnail_image;
    // Update the listing details
    $updateStmt = $conn->prepare("
        UPDATE listings 
        SET Title = ?, Description = ?, Price = ?, State = ?, City = ?, Thumbnail_Image = ? 
        WHERE Listing_ID = ? AND User_ID = ?
    ");
    $updateStmt->bind_param("ssdssiii", $title, $description, $price, $state, $city, $newThumbnail, $listing_id, $user_id);
    if (!$updateStmt->execute()) {
        echo "Error updating listing.";
    }
    $updateStmt->close();
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
                    $imageUrl = $targetFilePath;
                    // Add image to the database
                    $imageStmt = $conn->prepare("
                        INSERT INTO images (Image_URL, Listing_ID) 
                        VALUES (?, ?)
                    ");
                    $imageStmt->bind_param("si", $imageUrl, $listing_id);
                    $imageStmt->execute();
                    $imageStmt->close();
                }
            }
        }
    }
    header("Location: user_dashboard.php");
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
</head>

<body>
<?php include 'header.php'; ?>

<div class="edit-listing-container">
    <form id="edit-listing-form" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="listing_id" value="<?= htmlspecialchars($listing_id); ?>">

                <!-- Thumbnail Selection -->
                <div class="form-group">
            <label>Thumbnail:</label>
            <div class="thumbnail-selection">
                <img src="<?= htmlspecialchars($thumbnail_image); ?>" class="current-thumbnail" alt="Current Thumbnail">
                <?php foreach ($additionalImages as $image): ?>
                    <input type="radio" id="thumb-<?= htmlspecialchars($image); ?>" name="thumbnail" value="<?= htmlspecialchars($image); ?>" <?= $thumbnail_image === $image ? "checked" : ""; ?>>
                    <label for="thumb-<?= htmlspecialchars($image); ?>">
                        <img src="<?= htmlspecialchars($image); ?>" class="thumbnail-option" alt="Thumbnail Option">
                    </label>
                <?php endforeach; ?>
            </div>
     
     
        <!-- Image Preview -->
        <div id="imagePreviewContainer">
            <?php foreach ($additionalImages as $image): ?>
                <img src="<?= htmlspecialchars($image); ?>" class="preview-image" alt="Image Preview">
            <?php endforeach; ?>
        </div>
     
        </div>
        <!-- Image Upload -->
        <div class="file-upload-container">
            <label for="images">Upload New Images:</label>
            <input type="file" id="images" name="images[]" multiple>
        </div>


        <!-- Title -->
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($title); ?>" required>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?= htmlspecialchars($description); ?></textarea>
        </div>

        <!-- Price -->
        <div class="form-group">
            <label for="price">Price:</label>
            <input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($price); ?>" required>
        </div>

        <!-- State -->
        <div class="form-group">
            <label for="state">State:</label>
            <select id="state" name="state" required>
                <option value="AL" <?= $state === "AL" ? "selected" : ""; ?>>Alabama</option>
                <option value="AK" <?= $state === "AK" ? "selected" : ""; ?>>Alaska</option>
                <option value="AZ" <?= $state === "AZ" ? "selected" : ""; ?>>Arizona</option>
                <option value="AR" <?= $state === "AR" ? "selected" : ""; ?>>Arkansas</option>
                <option value="CA" <?= $state === "CA" ? "selected" : ""; ?>>California</option>
                <!-- Add more states -->
            </select>
        </div>

        <!-- City -->
        <div class="form-group">
            <label for="city">City:</label>
            <input type="text" id="city" name="city" value="<?= htmlspecialchars($city); ?>" required>
        </div>


        <!-- Submit Button -->
        <button type="submit" name="update_listing">Update Listing</button>
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

<?php include 'footer.php'; ?>
</body>
</html>

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