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
    <script src="dynamic_cities.js"></script>

    <script>
        // JavaScript function to preview selected image files before upload
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
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
        <textarea id="description" name="description"
            required><?php echo htmlspecialchars($listing['description']); ?></textarea>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($listing['price']); ?>"
            required>

        <select id="state" name="state" required>
            <option value="">--Select State--</option>
            <option value="AL">Alabama</option>
            <option value="AK">Alaska</option>
            <!-- Add other states as needed -->
        </select>
        <select id="city" name="city" placeholder="City" required>
            <option Value="">--Select City--</option>


            <label for="new_image">Upload New Image:</label>
            <input type="file" id="new_image" name="new_image" accept="image/*" onchange="previewImage(event)">
            <!-- Image preview element -->
            <img id="imagePreview" src="#" alt="Image Preview"
                style="display: none; width: 150px; height: auto; margin-top: 10px;">

            <form action="edit_listing.php?listing_id=<?php echo htmlspecialchars($listing_id); ?>" method="POST"
                enctype="multipart/form-data" class="edit-listing-form">
                <button type="submit" class="pill-button-edit">Update</button>
                </div>
            </form>
            </div>

            <div id="imagePreviewContainer"></div>


            <script src="dynamic_cities.js"></script>
            <script src="image_preview.js"></script>
</body>

<?php include 'footer.php'; ?>

</html>