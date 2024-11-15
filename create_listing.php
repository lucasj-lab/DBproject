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
function getCategoryID($conn, $categoryName)
{
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
// Check if Imagick is installed and define the conversion function if it is
if (class_exists('Imagick')) {
    function convertToJpeg($inputPath, $outputPath) {
        $imagick = new Imagick($inputPath);
        $imagick->setImageFormat('jpeg');
        $imagick->writeImage($outputPath);
        $imagick->destroy();
    }
} else {
    echo "Warning: Imagick is not installed, HEIC/HEIF images may not be supported.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve user ID from session
    $user_id = $_SESSION['user_id'] ?? null;

    // Retrieve form data and check if they exist to prevent warnings
    $category = $_POST['category'] ?? null;
    $title = $_POST['title'] ?? null;
    $description = $_POST['description'] ?? null;
    $price = $_POST['price'] ?? null;
    $state = $_POST['state'] ?? null;
    $city = $_POST['city'] ?? null;

    // Validate that all required fields are present
    if (!$user_id || !$category || !$title || !$description || !$price || !$state || !$city) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit();
    }

    // Get the Category_ID using the function
    $category_id = getCategoryID($conn, $category);

    if ($category_id === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid category selected.']);
        exit();
    }

    // Prepare and execute the INSERT query
    $stmt = $conn->prepare("INSERT INTO listings (Title, Description, Price, Date_Posted, User_ID, Category_ID, State, City) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)");
    
    // Check if the statement was prepared successfully
    if ($stmt) {
        $stmt->bind_param("ssissss", $title, $description, $price, $user_id, $category_id, $state, $city);

        // Execute the statement and check for success
        if ($stmt->execute()) {
            $listing_id = $stmt->insert_id;

            // Handle image uploads if provided
            if (!empty($_FILES['images']['name'][0])) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/heic', 'image/heif'];
                $uploadDirectory = 'uploads/';
                if (!is_dir($uploadDirectory)) {
                    mkdir($uploadDirectory, 0777, true);
                }

                foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                    $fileType = mime_content_type($tmpName);

                    // Only process if the file is of an allowed type
                    if (in_array($fileType, $allowedTypes)) {
                        $imageName = basename($_FILES['images']['name'][$key]);
                        $uniqueImageName = time() . "_" . $imageName;
                        $targetFilePath = $uploadDirectory . $uniqueImageName;

                        if (move_uploaded_file($tmpName, $targetFilePath)) {
                            $imageUrl = $targetFilePath;

                            // Insert image data into images table
                            $imageSql = "INSERT INTO images (image_url, listing_id) VALUES (?, ?)";
                            $imgStmt = $conn->prepare($imageSql);
                            $imgStmt->bind_param("si", $imageUrl, $listing_id);
                            $imgStmt->execute();
                            $imgStmt->close();
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => "File type not allowed: " . htmlspecialchars($fileType)]);
                        exit();
                    }
                }
            }
            echo json_encode(['success' => true, 'message' => 'Listing created successfully!', 'listing_id' => $listing_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create listing.']);
        }

        $stmt->close(); // Close the statement
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: Failed to prepare statement.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
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
                <!-- Add other states as needed -->
            </select>

            <div class="listing-city-group">
                <select id="city-dropdown" name="city" required>
                    <option value="">--Select City--</option>
                </select>
            </div>

            <div class="file-upload-container">
            <label class="form-label" for="images"></label>
            <input type="file" id="images" name="images[]" class="file-input" accept=".jpg, .jpeg, .png, .heic, .heif" multiple>
            <label for="images" class="file-upload-button">Choose Files</label>
            <span class="file-upload-text" id="file-upload-text"></span>
        </div>
        <div id="imagePreviewContainer"></div> <!-- Image Previews -->
    </div>
    <div class="btn-container">
        <button type="submit">Submit</button>
    </div>
</form>
</div>       
</div>
    </form>

    <script>
        // Data structure with states and corresponding cities
        const statesAndCities = {
            "AL": ["Birmingham", "Montgomery", "Mobile", "Huntsville", "Tuscaloosa"],
            "AK": ["Anchorage", "Fairbanks", "Juneau", "Sitka", "Ketchikan"],
            "AZ": ["Phoenix", "Tucson", "Mesa", "Chandler", "Glendale"],
            "AR": ["Little Rock", "Fort Smith", "Fayetteville", "Springdale", "Jonesboro"],
            "CA": ["Los Angeles", "San Diego", "San Jose", "San Francisco", "Fresno"],
            "CO": ["Denver", "Colorado Springs", "Aurora", "Fort Collins", "Lakewood"]
            // Add other states and cities as needed
        };

        // Function to update city dropdown based on selected state
        function updateCities() {
            const stateSelect = document.getElementById('state');
            const cityDropdown = document.getElementById('city-dropdown');
            const selectedState = stateSelect.value;

            // Clear previous city options
            cityDropdown.innerHTML = '<option value="">--Select City--</option>';

            // Populate city dropdown if a valid state is selected
            if (selectedState && statesAndCities[selectedState]) {
                const cities = statesAndCities[selectedState];
                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    cityDropdown.appendChild(option);
                });
            }
        }

        // Debugging: Ensure JavaScript is loaded and functions are called correctly
        document.addEventListener("DOMContentLoaded", function() {
            console.log("JavaScript loaded, ready to update cities.");
        });
        function updateCities() {
    const stateSelect = document.getElementById('state');
    const cityDropdown = document.getElementById('city-dropdown');
    const selectedState = stateSelect.value;

    console.log("Selected state:", selectedState); // Debugging log

    // Clear previous city options
    cityDropdown.innerHTML = '<option value="">--Select City--</option>';

    // Populate city dropdown if a valid state is selected
    if (selectedState && statesAndCities[selectedState]) {
        const cities = statesAndCities[selectedState];
        cities.forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            cityDropdown.appendChild(option);
        });
        console.log("Cities added:", cities); // Debugging log
    }
}


document.addEventListener("DOMContentLoaded", function () {
    const imageInput = document.querySelector("input[name='images[]']");
    const previewContainer = document.getElementById("imagePreviewContainer");
    const fileText = document.getElementById("file-upload-text");

    imageInput.addEventListener("change", function () {
        previewContainer.innerHTML = ""; // Clear previous previews
        fileText.textContent = this.files.length > 0 ? `${this.files.length} files selected` : "No files chosen";

        Array.from(this.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.createElement("img");
                img.src = e.target.result;
                img.classList.add("preview-image"); // Ensure styling for .preview-image is in CSS
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        });

        // Ensure the container respects scrollable behavior
        previewContainer.scrollLeft = 0; // Reset scroll position when new images are loaded
    });
});


        function showSuccessModal() {
            document.getElementById("successModal").style.display = "block";
        }


        showSuccessModal();




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

<?php include 'footer.php'; ?>

</html>