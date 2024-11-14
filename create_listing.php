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

if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    $user_id = $_SESSION['user_id']; // Retrieve user ID from session
    $category = $_POST['category'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $state = $_POST['state'];
    $city = $_POST['city']; // Only use city dropdown selection

    // Get the Category_ID using the function
    $category_id = getCategoryID($conn, $category);

    if ($category_id === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid category selected.']);
    } else {
        // Insert new listing
        $stmt = $conn->prepare("INSERT INTO listings (Title, Description, Price, Date_Posted, User_ID, Category_ID, State, City) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)");
        $stmt->bind_param("ssissss", $title, $description, $price, $user_id, $category_id, $state, $city);

        if ($stmt->execute()) {
            $listing_id = $stmt->insert_id;
            if (!empty($_FILES['images']['name'][0])) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/heic', 'image/heif'];
                $uploadDirectory = 'uploads/';
                if (!is_dir($uploadDirectory)) {
                    mkdir($uploadDirectory, 0777, true);
                }
            
                foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                    $fileType = mime_content_type($tmpName); // Check the MIME type of the file
            
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
                        }
                    } else {
                        echo "File type not allowed: " . htmlspecialchars($fileType);
                        exit();
                    }
                }
            }
            

    }

    $stmt->close();
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

            <label for="images">Upload Images:</label>
    <input type="file" id="images" name="images[]" accept=".jpg, .jpeg, .png, .heic, .heif" multiple>
    <div id="imagePreviewContainer"></div> <!-- Container for image previews -->
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

        imageInput.addEventListener("change", function () {
            previewContainer.innerHTML = ""; // Clear previous previews
            Array.from(imageInput.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.createElement("img");
                    img.src = e.target.result;
                    img.classList.add("preview-image"); // Ensure styling for .preview-image is in CSS
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });


        function showSuccessModal() {
            document.getElementById("successModal").style.display = "block";
        }

        // Simulate showing the modal after successful listing creation
        // In real use, this function call should be triggered only if the server returns success
        showSuccessModal();

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

<?php include 'footer.php'; ?>

</html>