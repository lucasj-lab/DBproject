<?php
session_start(); // Start session to access user information

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Not Logged In</title>
        <link rel='stylesheet' href='styles.css'>
    </head>
    <body>
        <div class='redirect-message-container'>
            <div class='redirect-message'>
                <h2>You must be logged in to create a listing.</h2>
                <p>Please <a href='login.php'>log in</a> or <a href='signup.php'>sign up</a> to continue.</p>
            </div>
        </div>
    </body>
    </html>";
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'database_connection.php';

// Function to get Category_ID from Category table
function getCategoryID($conn, $categoryName) {
    $stmt = $conn->prepare("SELECT Category_ID FROM category WHERE Category_Name = ?");
    $stmt->bind_param("s", $categoryName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['Category_ID'];
    } else {
        return false; // Category not found
    }
}

// Check if POST request is made for creating a new listing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    $category = $_POST['category'] ?? null;
    $title = $_POST['title'] ?? null;
    $description = $_POST['description'] ?? null;
    $price = $_POST['price'] ?? null;
    $state = $_POST['state'] ?? null;
    $city = $_POST['city'] ?? null;

    // Validate required fields
    if (!$user_id || !$category || !$title || !$description || !$price || !$state || !$city) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit();
    }

    // Get Category_ID
    $category_id = getCategoryID($conn, $category);
    if ($category_id === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid category selected.']);
        exit();
    }

    // Prepare and execute the INSERT query
    $stmt = $conn->prepare("INSERT INTO listings (Title, Description, Price, Date_Posted, User_ID, Category_ID, State, City) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssissss", $title, $description, $price, $user_id, $category_id, $state, $city);
        if ($stmt->execute()) {
            $listing_id = $stmt->insert_id;

            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/heic', 'image/heif'];
                $uploadDirectory = 'uploads/';
                if (!is_dir($uploadDirectory)) {
                    mkdir($uploadDirectory, 0777, true);
                }

                foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                    $fileType = mime_content_type($tmpName);
                    if (in_array($fileType, $allowedTypes)) {
                        $imageName = basename($_FILES['images']['name'][$key]);
                        $uniqueImageName = time() . "_" . $imageName;
                        $targetFilePath = $uploadDirectory . $uniqueImageName;

                        if (move_uploaded_file($tmpName, $targetFilePath)) {
                            $imageUrl = $targetFilePath;
                            $imageSql = "INSERT INTO images (image_url, listing_id) VALUES (?, ?)";
                            $imgStmt = $conn->prepare($imageSql);
                            $imgStmt->bind_param("si", $imageUrl, $listing_id);
                            $imgStmt->execute();
                            $imgStmt->close();
                        }
                    }
                }
            }
            echo json_encode(['success' => true, 'message' => 'Listing created successfully!', 'listing_id' => $listing_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create listing.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: Failed to prepare statement.']);
    }
}

// Fetch existing listings for display
$listings = [];
$sql = "SELECT Title, Description, Price, User_ID, Category_ID, City, State, Date_Posted, Image_ID FROM listings";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $listings = $result->fetch_all(MYSQLI_ASSOC);
} else {
    echo "No listings found.";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Listing</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="create-listing-container">
        <h1 class="edit-listing-title">Create New Listing</h1>
        <form id="create-listing-form" action="create_listing.php" method="POST" enctype="multipart/form-data">
            <div class="listing-form-group">
                <select id="category" name="category" required>
                    <option value="">--Select Category--</option>
                    <option value="Auto">Auto</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Furniture">Furniture</option>
                    <option value="Other">Other</option>
                </select>
                <input type="text" id="title" name="title" placeholder="Title" required>
                <textarea id="description" name="description" rows="4" placeholder="Description" required></textarea>
                <input type="number" step="0.01" id="price" name="price" placeholder="Price" required>
                <select id="state" name="state" onchange="updateCities()" required>
                    <option value="">--Select State--</option>
                    <option value="AL">Alabama</option>
                    <option value="AK">Alaska</option>
                    <option value="AZ">Arizona</option>
                    <option value="AR">Arkansas</option>
                    <option value="CA">California</option>
                    <option value="CO">Colorado</option>
                </select>
                <div class="listing-city-group">
                    <select id="city-dropdown" name="city" required>
                        <option value="">--Select City--</option>
                    </select>
                </div>
                <label for="images">Upload Images:</label>
                <input type="file" id="images" name="images[]" accept=".jpg, .jpeg, .png, .heic, .heif" multiple>
                <div id="imagePreviewContainer"></div>
                <button type="submit">Submit</button>
            </div>
        </form>
    </div>

    <div class="listings-container">
        <h2>Active Listings</h2>
        <div id="listings">
            <?php if (!empty($listings)): ?>
                <?php foreach ($listings as $listing): ?>
                    <div class="listing-container">
                        <img src="<?= htmlspecialchars($listing['Image_URL'] ?? 'no_image.png'); ?>" alt="Listing Image" class="listing-image">
                        <h3><?= htmlspecialchars($listing['Title']); ?></h3>
                        <p><strong>Description:</strong> <?= htmlspecialchars($listing['Description']); ?></p>
                        <p><strong>Price:</strong> $<?= htmlspecialchars($listing['Price']); ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($listing['City'] . ', ' . $listing['State']); ?></p>
                        <p><strong>Date Posted:</strong> <?= (new DateTime($listing['Date_Posted']))->format('l, F jS, Y'); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No listings available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Update city dropdown based on selected state
        const statesAndCities = {
            "AL": ["Birmingham", "Montgomery", "Mobile"],
            "AK": ["Anchorage", "Fairbanks", "Juneau"],
            "AZ": ["Phoenix", "Tucson", "Mesa"],
            "AR": ["Little Rock", "Fort Smith", "Fayetteville"],
            "CA": ["Los Angeles", "San Diego", "San Francisco"],
            "CO": ["Denver", "Colorado Springs", "Aurora"]
        };

        function updateCities() {
            const stateSelect = document.getElementById('state');
            const cityDropdown = document.getElementById('city-dropdown');
            const selectedState = stateSelect.value;

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

        document.addEventListener("DOMContentLoaded", function() {
            const imageInput = document.getElementById("images");
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
        #imagePreviewContainer { display: flex; gap: 10px; margin-top: 10px; }
        .preview-image { max-width: 100px; max-height: 100px; object-fit: cover; }
    </style>
</body>
</html>
