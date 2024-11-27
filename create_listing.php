<?php
session_start(); // Start session to access user information

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'database_connection.php';

// Function to get Category_ID from the Category table
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

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $category = $_POST['category'] ?? null;
    $title = $_POST['title'] ?? null;
    $description = $_POST['description'] ?? null;
    $price = $_POST['price'] ?? null;
    $state = $_POST['state'] ?? null;
    $city = $_POST['city'] ?? null;

    if (!$user_id || !$category || !$title || !$description || !$price || !$state || !$city) {
        echo "<script>alert('All fields are required.'); window.location.href='create_listing.php';</script>";
        exit();
    }

    // Get the Category_ID
    $category_id = getCategoryID($conn, $category);
    if ($category_id === false) {
        echo "<script>alert('Invalid category selected.'); window.location.href='create_listing.php';</script>";
        exit();
    }

    // Insert the listing
    $stmt = $conn->prepare("INSERT INTO listings (Title, Description, Price, Date_Posted, User_ID, Category_ID, State, City) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssissss", $title, $description, $price, $user_id, $category_id, $state, $city);
        if ($stmt->execute()) {
            $listing_id = $stmt->insert_id;

            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'image/heic', 'image/heif'];
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
                            $isThumbnail = ($key === 0) ? 1 : 0; // First image is the thumbnail
                            $imageUrl = $targetFilePath;

                            // Insert image into the images table
                            $imageSql = "INSERT INTO images (Listing_ID, Image_URL, Is_Thumbnail) VALUES (?, ?, ?)";
                            $imgStmt = $conn->prepare($imageSql);
                            $imgStmt->bind_param("isi", $listing_id, $imageUrl, $isThumbnail);
                            $imgStmt->execute();
                            $imgStmt->close();
                        }
                    } else {
                        echo "<script>alert('File type not allowed: $fileType.'); window.location.href='create_listing.php';</script>";
                        exit();
                    }
                }
            }

            echo "<script>alert('Listing created successfully!'); window.location.href='user_dashboard.php';</script>";
        } else {
            echo "<script>alert('Failed to create listing. Please try again.'); window.location.href='create_listing.php';</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Database error: Failed to prepare statement.'); window.location.href='create_listing.php';</script>";
    }
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
    <script>
        const statesAndCities = {
            "Alabama": ["Birmingham", "Montgomery", "Mobile", "Huntsville", "Tuscaloosa"],
            "Alaska": ["Anchorage", "Fairbanks", "Juneau", "Sitka", "Ketchikan"],
            "Arizona": ["Phoenix", "Tucson", "Mesa", "Chandler", "Glendale"],
            "California": ["Los Angeles", "San Diego", "San Jose", "San Francisco", "Fresno"],
            "Florida": ["Jacksonville", "Miami", "Tampa", "Orlando", "St. Petersburg"],
            "Georgia": ["Atlanta", "Augusta", "Savannah", "Columbus", "Macon"],
            // Add other states and cities here
        };

        function updateCities() {
            const stateSelect = document.getElementById('state');
            const citySelect = document.getElementById('city');
            const selectedState = stateSelect.value;

            citySelect.innerHTML = '<option value="">--Select City--</option>';

            if (statesAndCities[selectedState]) {
                statesAndCities[selectedState].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
            }
        }
    </script>
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="create-listing-container">
        <h1>Create New Listing</h1>
        <form action="create_listing.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="">--Select Category--</option>
                    <option value="Auto">Auto</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Furniture">Furniture</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="state">State:</label>
                <select id="state" name="state" onchange="updateCities()" required>
                    <option value="">--Select State--</option>
                    <option value="Alabama">Alabama</option>
                    <option value="Alaska">Alaska</option>
                    <option value="Arizona">Arizona</option>
                    <option value="California">California</option>
                    <option value="Florida">Florida</option>
                    <option value="Georgia">Georgia</option>
                </select>
            </div>
            <div class="form-group">
                <label for="city">City:</label>
                <select id="city" name="city" required>
                    <option value="">--Select City--</option>
                </select>
            </div>
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" step="0.01" id="price" name="price" required>
            </div>
            <div class="form-group">
                <label for="images">Images:</label>
                <input type="file" id="images" name="images[]" multiple>
            </div>
            <button type="submit">Create Listing</button>
        </form>
    </div>
</body>

<?php include 'footer.php'; ?>

</html>
