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
    // Update listing details
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $state = $_POST['state'];
    $city = $_POST['city'];
    $selectedThumbnail = $_POST['selected_thumbnail'];

    // Update listing information
    $updateStmt = $conn->prepare("
        UPDATE listings 
        SET Title = ?, Description = ?, Price = ?, State = ?, City = ? 
        WHERE Listing_ID = ? AND User_ID = ?
    ");
    $updateStmt->bind_param("ssdssii", $title, $description, $price, $state, $city, $listing_id, $user_id);
    $updateStmt->execute();
    $updateStmt->close();

    // Update thumbnail
    $resetThumbnailStmt = $conn->prepare("UPDATE images SET Is_Thumbnail = 0 WHERE Listing_ID = ?");
    $resetThumbnailStmt->bind_param("i", $listing_id);
    $resetThumbnailStmt->execute();

    $setThumbnailStmt = $conn->prepare("UPDATE images SET Is_Thumbnail = 1 WHERE Image_URL = ? AND Listing_ID = ?");
    $setThumbnailStmt->bind_param("si", $selectedThumbnail, $listing_id);
    $setThumbnailStmt->execute();

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
                    $isThumbnail = ($key === 0 && empty($selectedThumbnail)) ? 1 : 0;
                    $imageUrl = $targetFilePath;

                    $imageStmt = $conn->prepare("INSERT INTO images (Listing_ID, Image_URL, Is_Thumbnail) VALUES (?, ?, ?)");
                    $imageStmt->bind_param("isi", $listing_id, $imageUrl, $isThumbnail);
                    $imageStmt->execute();
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
    <h1>Edit Listing</h1>
    <form id="edit-listing-form" method="POST" enctype="multipart/form-data">
        <!-- Hidden input to pass listing ID -->
        <input type="hidden" name="listing_id" value="<?= htmlspecialchars($listing_id); ?>">

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
            <select id="state" name="state" required>
                <option value="AL" <?= $state === "AL" ? "selected" : ""; ?>>Alabama</option>
                <option value="AK" <?= $state === "AK" ? "selected" : ""; ?>>Alaska</option>
                <option value="AZ" <?= $state === "AZ" ? "selected" : ""; ?>>Arizona</option>
                <!-- Add more states -->
            </select>
        </div>

        <!-- City -->
        <div class="form-group">
            <label for="city">City:</label>
            <input type="text" id="city" name="city" value="<?= htmlspecialchars($city); ?>" required>
        </div>

        <!-- Thumbnail Selection -->
        <div class="form-group">
            <label>Thumbnail:</label>
            <div class="thumbnail-selection">
                <?php foreach ($images as $image): ?>
                    <img 
                        src="<?= htmlspecialchars($image['Image_URL']); ?>" 
                        class="thumbnail-option <?= $image['Is_Thumbnail'] ? 'selected-thumbnail' : ''; ?>" 
                        data-image-url="<?= htmlspecialchars($image['Image_URL']); ?>" 
                        alt="Thumbnail Option" 
                        onclick="selectThumbnail(this)">
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="selected_thumbnail" id="selectedThumbnail" value="">
        </div>

        <!-- File Upload -->
        <div class="file-upload-container">
            <label class="form-label" for="images">Upload New Images:</label>
            <input type="file" id="images" name="images[]" class="file-input" accept=".jpg, .jpeg, .png, .heic, .heif" multiple>
            <span class="file-upload-text" id="file-upload-text"></span>
        </div>

        <!-- Submit Button -->
        <div class="btn-container">
            <button type="submit" name="update_listing">Update Listing</button>
        </div>
    </form>
</div>

<script>
    function selectThumbnail(imgElement) {
        document.querySelectorAll('.thumbnail-option').forEach(img => img.classList.remove('selected-thumbnail'));
        imgElement.classList.add('selected-thumbnail');
        document.getElementById('selectedThumbnail').value = imgElement.getAttribute('data-image-url');
    }
</script>

<style>
    .thumbnail-option {
        border: 2px solid transparent;
        cursor: pointer;
        transition: 0.3s;
    }
    .selected-thumbnail {
        border: 2px solid blue;
    }
</style>

<?php include 'footer.php'; ?>
</body>
</html>
