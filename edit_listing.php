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

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing</title>
    <link rel="stylesheet" href="styles.css">
  
<style> 
/* Main Thumbnail Section */
.main-image-container {
    text-align: center;
    margin-bottom: 20px;
}

.main-image {
    width: 300px;
    height: auto;
    border: 2px solid #000;
    border-radius: 5px;
}

/* Image Gallery */
.image-gallery {
    display: flex;
    overflow-x: auto;
    overflow-y: hidden;
    gap: 10px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
    max-width: 100%; /* Ensure it stays within the container */
    box-sizing: border-box;
    white-space: nowrap; /* Prevent wrapping of images */
}

.image-item {
    flex-shrink: 0; /* Prevent images from shrinking */
    text-align: center;
    position: relative;
}

.image-item img {
    max-width: 100px;
    max-height: 100px;
    object-fit: cover;
    border: 1px solid #ddd;
    border-radius: 5px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.image-item img:hover {
    transform: scale(1.1);
    border-color: lightgray;
}

.thumbnail-radio {
    display: block;
    margin-top: 5px;
    cursor: pointer;
}

/* Scrollbar styling */
.image-gallery::-webkit-scrollbar {
    height: 8px; /* Scrollbar height */
}

.image-gallery::-webkit-scrollbar-thumb {
    background: #ccc; /* Scrollbar thumb color */
    border-radius: 4px; /* Rounded scrollbar */
}

.image-gallery::-webkit-scrollbar-thumb:hover {
    background: #aaa; /* Darker thumb on hover */
}

.image-gallery::-webkit-scrollbar-track {
    background: #f9f9f9; /* Scrollbar track color */
}
</style>
    <script>
        function updateCities() {
            const stateSelect = document.getElementById('state');
            const cityDropdown = document.getElementById('city-dropdown');
            const selectedState = stateSelect.value;

            const statesAndCities = {
                "AL": ["Birmingham", "Montgomery", "Mobile", "Huntsville", "Tuscaloosa"],
                "AK": ["Anchorage", "Fairbanks", "Juneau", "Sitka", "Ketchikan"],
                "AZ": ["Phoenix", "Tucson", "Mesa", "Chandler", "Glendale"],
                "AR": ["Little Rock", "Fort Smith", "Fayetteville", "Springdale", "Jonesboro"],
                "CA": ["Los Angeles", "San Diego", "San Jose", "San Francisco", "Fresno"],
                "CO": ["Denver", "Colorado Springs", "Aurora", "Fort Collins", "Lakewood"]
            };

            cityDropdown.innerHTML = '<option value="">--Select City--</option>';
            if (selectedState && statesAndCities[selectedState]) {
                statesAndCities[selectedState].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    cityDropdown.appendChild(option);
                });
            }
        }
    </script>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="edit-listing-container">
        <h1 class="edit-listing-title">Edit Listing</h1>
        <form id="create-listing-form" method="POST" enctype="multipart/form-data">
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

                <!-- Display current images as a gallery -->
                <div class="image-gallery">
                    <?php foreach ($images as $image): ?>
                        <div class="image-item">
                            <img src="<?= htmlspecialchars($image['Image_URL']); ?>" alt="Listing Image">
                            <input type="radio" name="selected_thumbnail" value="<?= $image['Image_ID']; ?>" <?= $image['Is_Thumbnail'] ? "checked" : ""; ?>>
                         
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Upload new images -->
                <div class="file-upload-container">
                    <input type="file" id="images" name="images[]" class="file-input" accept=".jpg, .jpeg, .png, .heic, .heif" multiple>
                    <label for="images" class="file-upload-button">Choose Files</label>
                    <span class="file-upload-text" id="file-upload-text"></span>
                </div>
                <div class="btn-container">
                <button type="submit">Update</button>
            </div>
            </div>
         
        </form>
    </div>
</body>

</html>