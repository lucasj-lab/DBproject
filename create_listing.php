
create_listings



<?php
session_start();
require 'database_connection.php';

// Enable error reporting and log to a specified file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/html/php-error.log'); // Update with the path to your log file

$uploadDir = '/var/www/html/uploads/';

/**
 * Retrieves the Category_ID from the category table based on the category name.
 *
 * @param mysqli $conn The database connection.
 * @param string $categoryName The name of the category to search for.
 * @return int|false Returns the Category_ID if found, or false if not.
 */
function getCategoryID($conn, $categoryName) {
    error_log("Retrieving Category ID for category: $categoryName");
    $stmt = $conn->prepare("SELECT Category_ID FROM category WHERE Category_Name = ?");
    if (!$stmt) {
        error_log("Prepare failed for getCategoryID: " . $conn->error);
        return false;
    }
    $stmt->bind_param("s", $categoryName);
    $stmt->execute();
    $result = $stmt->get_result();
    $category_id = $result->fetch_assoc()['Category_ID'] ?? false;
    $stmt->close();
    return $category_id;
}
if (!isset($_SESSION['user_id'])) {
    error_log("User is not logged in");
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


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("Form submitted: " . json_encode($_POST));
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $state = trim($_POST['state'] ?? '');
    $city = isset($_POST['city']) && trim($_POST['city']) !== '' ? trim($_POST['city']) : (isset($_POST['city-input']) ? trim($_POST['city-input']) : '');
    $category = ucfirst(strtolower(trim($_POST['category'] ?? '')));

    // Validate fields
    if (empty($title) || empty($description) || empty($price) || empty($state) || empty($city) || empty($category)) {
        error_log("Form validation failed: missing required fields");
        echo "<p>All fields are required.</p>";
        exit();
    }

    // Get Category_ID
    $category_id = getCategoryID($conn, $category);
    if ($category_id === false) {
        error_log("Invalid category selected: $category");
        echo "<p>Invalid category selected.</p>";
        exit();
    }

    // Insert listing into database
    $stmt = $conn->prepare("INSERT INTO listings (User_ID, Title, Description, Price, Date_Posted, Category_ID, State, City) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)");
    if (!$stmt) {
        error_log("Prepare failed for listing insertion: " . $conn->error);
        echo "<p>Database error: Unable to create listing.</p>";
        exit();
    }
    $stmt->bind_param("issdiss", $user_id, $title, $description, $price, $category_id, $state, $city);

    if ($stmt->execute()) {
        $listing_id = $stmt->insert_id;
        error_log("Listing created with ID: $listing_id");

        // Handle image uploads
        if (!empty($_FILES['images']['name'][0])) {
            $image_stmt = $conn->prepare("INSERT INTO images (Image_URL, Listing_ID) VALUES (?, ?)");
            if (!$image_stmt) {
                error_log("Prepare failed for image insertion: " . $conn->error);
                echo "<p>Error preparing image insertion.</p>";
                exit();
            }

            foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
                $fileName = basename($_FILES['images']['name'][$index]);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    $image_url = 'uploads/' . $fileName;
                    $image_stmt->bind_param("si", $image_url, $listing_id);
                    if ($image_stmt->execute()) {
                        error_log("Image uploaded successfully: $fileName");
                    } else {
                        error_log("Error inserting image into database: " . $image_stmt->error);
                    }
                } else {
                    error_log("Error moving uploaded file: $fileName");
                }
            }
            $image_stmt->close();
        }
    } else {
        error_log("Error executing listing insertion: " . $stmt->error);
        echo "<div class='alert alert-danger'>Database error: Unable to create listing.</div>";
    }

    $stmt->close();
    $conn->close();
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Create Listing</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <>
        <?php include 'header.php'; ?>
        
        <div class="creating-listing-form">
        <h2>Create a New Listing</h2>
            <form id="listing-form" action="create_listing.php" method="POST" enctype="multipart/form-data">
                <div class="listing-form-group">
                    <input type="text" id="title" name="title" placeholder="Title" required>
                    <select id="category" name="category" required>
                        <option value="">--Select Category--</option>
                        <option value="Auto">Auto</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Furniture">Furniture</option>
                        <option value="Other">Other</option>
                    </select>
                    <textarea id="description" name="description" rows="4" placeholder="Description" required></textarea>
                    <input type="number" step="0.01" id="price" name="price" placeholder="Price" required>
                    <select id="state" name="state" onchange="updateCities()" required>
                        <option value="">--Select State--</option>
                        <option value="AL">Alabama</option>
                        <option value="AK">Alaska</option>
                        <option value="AZ">Arizona</option>
                        <option value="AR">Arkansas</option>
                        <option value="CA">California</option>
                    </select>
                    <select id="city-dropdown" name="city">
                        <option value="">--Select City--</option>
                    </select>
                    <label for="images">Upload Images:</label>
                    <input type="file" id="images" name="images[]" multiple accept=".jpg, .jpeg, .png, .gif, .heic, .heif">
                    <button type="submit">Submit</button>
                </div>
            </form>
        </div>

        <script>
            const citiesByState = {
                'AL': ['Birmingham', 'Montgomery', 'Huntsville', 'Mobile', 'Tuscaloosa'],
                'AK': ['Anchorage', 'Fairbanks', 'Juneau', 'Sitka', 'Ketchikan'],
                'AZ': ['Phoenix', 'Tucson', 'Mesa', 'Chandler', 'Scottsdale'],
                'AR': ['Little Rock', 'Fort Smith', 'Fayetteville', 'Springdale', 'Jonesboro'],
                'CA': ['Los Angeles', 'San Francisco', 'San Diego', 'San Jose', 'Sacramento']
            };

            function updateCities() {
                const stateSelect = document.getElementById('state');
                const cityDropdown = document.getElementById('city-dropdown');
                const selectedState = stateSelect.value;

                cityDropdown.innerHTML = '<option value="">--Select City--</option>';

                if (selectedState && citiesByState[selectedState]) {
                    citiesByState[selectedState].forEach(city => {
                        const option = document.createElement('option');
                        option.value = city;
                        option.textContent = city;
                        cityDropdown.appendChild(option);
                    });
                }
            }
        </script>

        <?php include 'footer.php'; ?>
    </body>
    </html>
    <?php
}
?>
