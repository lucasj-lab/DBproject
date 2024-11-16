<?php
session_start(); // Start session to access user information

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

// Initialize variables for pre-filling the form
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
    if (isset($_POST['update_listing'])) {
        // Update listing details
        $title = $_POST['title'] ?? $title;
        $description = $_POST['description'] ?? $description;
        $price = $_POST['price'] ?? $price;
        $state = $_POST['state'] ?? $state;
        $city = $_POST['city'] ?? $city;

        $updateStmt = $conn->prepare("
            UPDATE listings 
            SET Title = ?, Description = ?, Price = ?, State = ?, City = ? 
            WHERE Listing_ID = ? AND User_ID = ?
        ");
        $updateStmt->bind_param("ssdssii", $title, $description, $price, $state, $city, $listing_id, $user_id);
        if ($updateStmt->execute()) {
            header("Location: user_dashboard.php");
            exit();
        } else {
            echo "Error updating listing.";
        }
        $updateStmt->close();
    }

    if (isset($_POST['update_thumbnail'])) {
        // Update thumbnail image
        $newThumbnail = $_POST['thumbnail'];
        $thumbStmt = $conn->prepare("
            UPDATE listings 
            SET Thumbnail_Image = ? 
            WHERE Listing_ID = ? AND User_ID = ?
        ");
        $thumbStmt->bind_param("sii", $newThumbnail, $listing_id, $user_id);
        if ($thumbStmt->execute()) {
            $thumbnail_image = $newThumbnail; // Update the variable for display
        } else {
            echo "Error updating thumbnail.";
        }
        $thumbStmt->close();
    }
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
    <!-- Form for updating the thumbnail -->
    <form id="edit-thumbnail-form" method="POST">
        <h2>Set Thumbnail</h2>
        <div class="form-group">
            <label for="thumbnail">Select Thumbnail:</label>
            <select name="thumbnail" id="thumbnail">
                <option value="<?= htmlspecialchars($thumbnail_image); ?>">Current Thumbnail</option>
                <?php foreach ($additionalImages as $image): ?>
                    <option value="<?= htmlspecialchars($image); ?>" <?= $thumbnail_image === $image ? "selected" : ""; ?>>
                        <?= htmlspecialchars(basename($image)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="update_thumbnail">Update Thumbnail</button>
    </form>

    <!-- Form for updating the listing -->
    <form id="edit-listing-form" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="listing_id" value="<?= htmlspecialchars($listing_id); ?>">

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
            <select id="state" name="state" onchange="updateCities()" required>
                <option value="AL" <?= $state === "AL" ? "selected" : ""; ?>>Alabama</option>
                <option value="AK" <?= $state === "AK" ? "selected" : ""; ?>>Alaska</option>
                <option value="AZ" <?= $state === "AZ" ? "selected" : ""; ?>>Arizona</option>
                <option value="AR" <?= $state === "AR" ? "selected" : ""; ?>>Arkansas</option>
                <option value="CA" <?= $state === "CA" ? "selected" : ""; ?>>California</option>
                <option value="CO" <?= $state === "CO" ? "selected" : ""; ?>>Colorado</option>
                <!-- Add more states -->
            </select>
        </div>

        <!-- City -->
        <div class="form-group">
            <label for="city">City:</label>
            <input type="text" id="city" name="city" value="<?= htmlspecialchars($city); ?>" required>
        </div>

        <!-- Image Upload -->
        <div class="file-upload-container">
            <label for="images">Upload New Images:</label>
            <input type="file" id="images" name="images[]" multiple>
        </div>

        <!-- Image Preview -->
        <div id="imagePreviewContainer">
            <?php foreach ($additionalImages as $image): ?>
                <img src="<?= htmlspecialchars($image); ?>" class="preview-image" alt="Image Preview">
            <?php endforeach; ?>
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
