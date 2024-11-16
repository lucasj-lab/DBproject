$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$stmt->bind_result($title, $description, $price, $state, $city, $thumbnail_image);
if (!$stmt->fetch()) {
    die("Listing not found or you do not have permission to edit this listing.");
}
$stmt->close();

// Fetch additional images
$imageStmt = $conn->prepare("SELECT Image_ID, Image_URL FROM images WHERE Listing_ID = ?");
$imageStmt->bind_param("i", $listing_id);
$imageStmt->execute();
$images = $imageStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$imageStmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update listing details
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $state = $_POST['state'] ?? '';
    $city = $_POST['city'] ?? '';
    $selected_thumbnail = $_POST['selected_thumbnail'] ?? null;
    $updateStmt = $conn->prepare("UPDATE listings SET Title = ?, Description = ?, Price = ?, State = ?, City = ?, Thumbnail_Image = ? WHERE Listing_ID = ? AND User_ID = ?");
    $updateStmt->bind_param("ssdssiii", $title, $description, $price, $state, $city, $selected_thumbnail, $listing_id, $user_id);
    if ($updateStmt->execute()) {
        header("Location: user_dashboard.php");
        exit();
    } else {
        $error_message = "Error updating listing.";
    }
    $updateStmt->close();
}

$conn->close();
@@ -123,152 +67,127 @@
</head>

<body>
<?php include 'header.php'; ?>
<div class="edit-listing-container">
    <h1>Edit Listing</h1>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="listing_id" value="<?= htmlspecialchars($listing_id); ?>">
        <input type="hidden" name="selected_thumbnail" id="selectedThumbnail" value="<?= htmlspecialchars($thumbnail_image); ?>">
        <!-- Title -->
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($title); ?>" required>
        </div>
        <!-- Description -->
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($description); ?></textarea>
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
                <!-- Add other states -->
            </select>
        </div>
        <!-- City -->
        <div class="form-group">
            <label for="city">City:</label>
            <select id="city-dropdown" name="city" required>
                <option value="<?= htmlspecialchars($city); ?>" selected><?= htmlspecialchars($city); ?></option>
            </select>
        </div>
        <!-- Image Selection -->
        <div id="imageSelectionContainer" class="image-selection-container">
            <?php foreach ($images as $image): ?>
                <img 
                    src="<?= htmlspecialchars($image['Image_URL']); ?>" 
                    class="thumbnail-image <?= $thumbnail_image === $image['Image_URL'] ? 'selected' : ''; ?>" 
                    data-image-id="<?= htmlspecialchars($image['Image_URL']); ?>" 
                    onclick="selectThumbnail(this)" 
                    alt="Image for selection"
                >
            <?php endforeach; ?>
        </div>
        <!-- Image Upload -->
        <div class="form-group">
            <label for="images">Upload New Images:</label>
            <input type="file" id="images" name="images[]" accept=".jpg, .jpeg, .png, .heic, .heif" multiple>
            <div id="imagePreviewContainer"></div>
        </div>
        <!-- Submit Button -->
        <div class="form-group">
            <button type="submit">Update Listing</button>
        </div>
    </form>
</div>
<script>
    // Handle thumbnail selection
    function selectThumbnail(imageElement) {
        document.querySelectorAll('.thumbnail-image').forEach(img => img.classList.remove('selected'));
        imageElement.classList.add('selected');
        document.getElementById('selectedThumbnail').value = imageElement.getAttribute('data-image-id');
    }
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