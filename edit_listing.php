<?php
session_start();
require 'database_connection.php';
include 'header.php';

// Ensure the user is logged in and has access to this listing
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the listing ID from the query string
$listing_id = $_GET['listing_id'];
$user_id = $_SESSION['user_id'];

// Fetch listing details and associated images
$sql = "SELECT listings.title, listings.description, listings.price, listings.city, listings.state, images.image_url 
        FROM listings 
        LEFT JOIN images ON listings.Listing_ID = images.Listing_ID 
        WHERE listings.Listing_ID = :listing_id AND listings.user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['listing_id' => $listing_id, 'user_id' => $user_id]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$listing) {
    echo "Listing not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Listing</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        // JavaScript function to preview selected image files before upload
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById("imagePreview").src = e.target.result;
                    document.getElementById("imagePreview").style.display = "block";
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</head>
<body>
<h1 class="edit-listing-title">Edit Your Listing</h1>

    <form action="update_listing.php" method="POST" enctype="multipart/form-data" class="edit-listing-container">
        <!-- Hidden input to keep the listing ID -->
        <input type="hidden" name="listing_id" value="<?php echo htmlspecialchars($listing_id); ?>">

        <label for="title">Title:</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($listing['title']); ?>" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($listing['description']); ?></textarea>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($listing['price']); ?>" required>

        <select id="state" name="state" required>
                    <option value="">--Select State--</option>
                    <option value="AL">Alabama</option>
                    <option value="AK">Alaska</option>
                    <!-- Add other states as needed -->
                </select>
                <select id="city" name="city" placeholder="City" required>
                    <option Value="">--Select City--</option>

                    <div class="file-upload-container">
                        <input type="file" id="fileInput" name="files[]" class="file-input" multiple>
                        <label for="fileInput" class="file-upload-button">Choose Files</label>
                        <span class="file-upload-text">No files chosen</span>
                    </div>

            </div>
        </form>
    </div>
    <script>
document.getElementById('fileInput').addEventListener('change', function() {
    const fileNames = Array.from(this.files).map(file => file.name).join(', ');
    document.querySelector('.file-upload-text').textContent = fileNames || "No files chosen";
});
</script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const stateDropdown = document.getElementById("state");
            const cityDropdown = document.getElementById("city");

            // Predefined example city data by state
            const citiesByState = {
                "AL": ["Birmingham", "Montgomery", "Mobile"],
                "AK": ["Anchorage", "Juneau", "Fairbanks"],
                "AZ": ["Phoenix", "Tucson", "Mesa"],
                "AR": ["Little Rock", "Fayetteville", "Springdale"],
                "CA": ["Los Angeles", "San Francisco", "San Diego"]
                // Add more states and cities as needed
            };

            stateDropdown.addEventListener("change", function () {
                const selectedState = stateDropdown.value;
                const cities = citiesByState[selectedState] || [];
                cityDropdown.innerHTML = '<option value="">--Select City--</option>';

                cities.forEach(city => {
                    const option = document.createElement("option");
                    option.value = city;
                    option.textContent = city;
                    cityDropdown.appendChild(option);
                });
            });
        });
    </script>

    <div id="imagePreviewContainer"></div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const imageInput = document.querySelector("input[name='images[]']");
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

         <h3 class="edit-listing-subtitle">Current Image</h3>
        <div class="image-section">

            <?php if (!empty($listing['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($listing['image_url']); ?>" alt="Current Image" class="current-image" style="width: 150px; height: auto;">
            <?php else: ?>
                <p>No image available for this listing.</p>
            <?php endif; ?>
        </div>

        <!-- New image upload with preview -->
        <label for="new_image">Upload New Image:</label>
        <input type="file" id="new_image" name="new_image" accept="image/*" onchange="previewImage(event)">
        <!-- Image preview element -->
        <img id="imagePreview" src="#" alt="Image Preview" style="display: none; width: 150px; height: auto; margin-top: 10px;">

        <form action="edit_listing.php?listing_id=<?php echo htmlspecialchars($listing_id); ?>" method="POST" enctype="multipart/form-data" class="edit-listing-form">
        <button type="submit" class="pill-button-edit">Update</button>
    </form>

    <!-- Include the footer -->
    <?php include 'footer.php'; ?>
</body>
</html>
