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

// Initialize variables
$title = $description = $city = $state = "";
$price = 0.0;

// Fetch listing details
$stmt = $conn->prepare("SELECT Title, Description, Price, City, State FROM listings WHERE Listing_ID = ? AND User_ID = ?");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$stmt->bind_result($title, $description, $price, $city, $state);

if (!$stmt->fetch()) {
    $error_message = "Listing not found or you do not have permission to edit this listing.";
}

$stmt->close();

// Handle form submission for updating the listing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';

    // Update the listing in the database
    $updateStmt = $conn->prepare("UPDATE listings SET Title = ?, Description = ?, Price = ?, City = ?, State = ? WHERE Listing_ID = ? AND User_ID = ?");
    $updateStmt->bind_param("ssdssii", $title, $description, $price, $city, $state, $listing_id, $user_id);

    if ($updateStmt->execute()) {
        header("Location: user_dashboard.php"); // Redirect after successful update
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

    <form id="create-listing-container">
        <h1 class="edit-listing-e">Edit Your Listing</h1>

        <?php if (!empty($error_message)) : ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form action="update_listing.php" method="POST" enctype="multipart/form-data" class="edit-listing-container">
            <div class="listing-form-group">
                <!-- Hidden input to keep the listing ID -->
                <input type="hidden" name="listing_id" value="<?php echo htmlspecialchars($listing_id); ?>">

                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>

                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($description); ?></textarea>

                <label for="price">Price:</label>
                <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" required>

                <label for="state">State:</label>
                <select id="state" name="state" onchange="updateCities()" required>
                    <option value="">--Select State--</option>
                    <option value="AL" <?php echo $state == "AL" ? "selected" : ""; ?>>Alabama</option>
                    <option value="AK" <?php echo $state == "AK" ? "selected" : ""; ?>>Alaska</option>
                    <option value="AZ" <?php echo $state == "AZ" ? "selected" : ""; ?>>Arizona</option>
                    <option value="AR" <?php echo $state == "AR" ? "selected" : ""; ?>>Arkansas</option>
                    <option value="CA" <?php echo $state == "CA" ? "selected" : ""; ?>>California</option>
                    <option value="CO" <?php echo $state == "CO" ? "selected" : ""; ?>>Colorado</option>
                    <!-- Add other states as needed -->
                </select>

                <label for="city">City:</label>
                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>" required>

                <div class="btn-container">
                    <label for="images">Upload Images:</label>
                    <input type="file" id="images" name="images[]" accept=".jpg, .jpeg, .png, .heic, .heif" multiple>
                </div>

                <div id="imagePreviewContainer"></div> <!-- Container for image previews -->
                <div class="btn-container">
                    <button type="submit">Update Listing</button>
                </div>
            </div>
        </form>
    </form>

    <script>
        const statesAndCities = {
            "AL": ["Birmingham", "Montgomery", "Mobile", "Huntsville", "Tuscaloosa"],
            "AK": ["Anchorage", "Fairbanks", "Juneau", "Sitka", "Ketchikan"],
            "AZ": ["Phoenix", "Tucson", "Mesa", "Chandler", "Glendale"],
            "AR": ["Little Rock", "Fort Smith", "Fayetteville", "Springdale", "Jonesboro"],
            "CA": ["Los Angeles", "San Diego", "San Francisco", "Fresno"],
            "CO": ["Denver", "Colorado Springs", "Aurora", "Lakewood"]
        };

        function updateCities() {
            const stateSelect = document.getElementById('state');
            const cityDropdown = document.getElementById('city-dropdown');
            const selectedState = stateSelect.value;

            cityDropdown.innerHTML = '<option value="">--Select City--</option>';

            if (statesAndCities[selectedState]) {
                statesAndCities[selectedState].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    cityDropdown.appendChild(option);
                });
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
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

    <style>
        #imagePreviewContainer {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .preview-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>

</body>

<footer>
    <?php include 'footer.php'; ?>
</footer>

</html>
