<?php
session_start();
require 'database_connection.php';

// Error reporting setup
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Directory for image uploads
$uploadDir = '/var/www/html/uploads/';

/**
 * Helper function to get Category_ID based on the category name.
 */
function getCategoryID($conn, $categoryName)
{
    $stmt = $conn->prepare("SELECT Category_ID FROM category WHERE Category_Name = ?");
    $stmt->bind_param("s", $categoryName);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['Category_ID'] ?? false;
}

if (!isset($_SESSION['user_id'])) {
    echo "<p>You must be logged in to create a listing.</p>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieving and validating form data
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $state = trim($_POST['state'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $category = ucfirst(strtolower(trim($_POST['category'] ?? '')));

    // Check required fields
    if (empty($title) || empty($description) || empty($price) || empty($state) || empty($city) || empty($category)) {
        echo "<p>All fields are required.</p>";
        exit();
    }

    // Get Category_ID
    $category_id = getCategoryID($conn, $category);
    if ($category_id === false) {
        echo "<p>Invalid category selected.</p>";
        exit();
    }

    // Insert listing data
    $stmt = $conn->prepare("INSERT INTO listings (User_ID, Title, Description, Price, Date_Posted, Category_ID, State, City) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)");
    $stmt->bind_param("issdiss", $user_id, $title, $description, $price, $category_id, $state, $city);
    if ($stmt->execute()) {
        $listing_id = $stmt->insert_id;

        // Handle image uploads
        if (!empty($_FILES['images']['name'][0])) {
            $image_stmt = $conn->prepare("INSERT INTO images (Image_URL, Listing_ID) VALUES (?, ?)");

            foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
                $fileName = basename($_FILES['images']['name'][$index]);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    $image_url = 'uploads/' . $fileName;
                    $image_stmt->bind_param("si", $image_url, $listing_id);
                    $image_stmt->execute();
                } else {
                    echo "<p>Failed to upload $fileName.</p>";
                }
            }
            $image_stmt->close();
        }
    } else {
        echo "<p>Error creating listing.</p>";
    }
    $stmt->close();
    $conn->close();
} else {
    // Display listing form if not a POST request
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>Create Listing</title>
    </head>
    <body>
    <header>
            <?php include 'header.php'; ?>
        </header>
        
        <div class="creating-listing-form">
        <h2>Create a New Listing</h2>
        
        <form action="create_listing.php" method="POST" enctype="multipart/form-data">
            <!-- Listing form fields -->
            <input type="text" name="title" placeholder="Title" required>
            <select name="category" required>
                <option value="">Select Category</option>
                <option value="Auto">Auto</option>
                <!-- Additional categories here -->
            </select>
            <textarea name="description" placeholder="Description" required></textarea>
            <input type="number" step="0.01" name="price" placeholder="Price" required>
            <input type="text" name="state" placeholder="State" required>
            <input type="text" name="city" placeholder="City" required>
            <input type="file" name="images[]" multiple accept="image/*">
            <button type="submit">Create Listing</button>
        </form>
    </div>

        <footer>
            <?php include 'footer.php'; ?>
        </footer>
    </body>

    </html>
    <?php
}
?>