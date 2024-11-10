<?php
// Start the session
session_start();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/html/php-error.log'); 
// Include the database connection file
require 'database_connection.php';
// Check if user_id is available in session
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
    
    // Fetch form data and sanitize
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $state = trim($_POST['state'] ?? '');
    $city = isset($_POST['city']) && trim($_POST['city']) !== '' ? trim($_POST['city']) : (isset($_POST['city-input']) ? trim($_POST['city-input']) : '');
    $category = ucfirst(strtolower(trim($_POST['category'] ?? '')));

    // Validate the form data
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

    // Insert listing into the database
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
                $targetPath = '/var/www/html/uploads/' . $fileName;

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
    <body>
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

                    <!-- Optional image upload -->
                    <input type="file" name="images[]" multiple>

                    <button type="submit">Submit Listing</button>
                </div>
            </form>
        </div>

        <?php include 'footer.php'; ?>
    </body>
    </html>
<?php
}
?>
