<?php
session_start();
require 'database_connection.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to create a listing.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process the form submission
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $state = trim($_POST['state']);
    $city = trim($_POST['city']) ?: trim($_POST['city-input']);
    $category = ucfirst(strtolower(trim($_POST['category'])));

    // Check required fields
    if (empty($title) || empty($description) || empty($price) || empty($state) || empty($city)) {
        echo "<p>All fields are required.</p>";
        exit();
    }

    // Get Category_ID
    $category_id = getCategoryID($conn, $category);
    if ($category_id === false) {
        echo "<p>Invalid category selected.</p>";
        exit();
    }

    // Insert new listing into database
    $stmt = $conn->prepare("INSERT INTO listings (User_ID, Title, Description, Price, Date_Posted, Category_ID, State, City) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)");
    $stmt->bind_param("issdiss", $user_id, $title, $description, $price, $category_id, $state, $city);

    if ($stmt->execute()) {
        echo "<p>Listing created successfully! <a href='account.php'>View your listings.</a></p>";
    } else {
        echo "<p>Database error: Unable to create listing.</p>";
    }

    $stmt->close();
    $conn->close();
} else {
    // Display the HTML form for creating a new listing
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Listing</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOMt23cez/3paNdF+K9aIIXUXl09Aq5AxlE9+y5T" crossorigin="anonymous">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="post-ad">
        <h2>Post Your Ad</h2>
        <p>Please <a href="signup.php">Sign up</a> to create a new listing.</p>
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
                    <!-- Add other states as needed -->
                </select>
                <div class="listing-city-group">
                    <select id="city-dropdown" name="city" onchange="toggleInput()" required>
                        <option value="">--Select City--</option>
                    </select>
                    <input type="text" id="city-input" name="city-input" placeholder="Type your city if not listed" oninput="clearDropdown()">
                </div>
                <label for="images">Upload Images:</label>
                <input type="file" id="images" name="images[]" multiple accept=".jpg, .jpeg, .png, .gif, .heic, .heif">
                <button type="submit">Submit</button>
            </div>
        </form>
    </div>

    <footer>
    <?php include 'footer.php'; ?>
</footer>
    <script src="script.js"></script>
</body>
</html>
<?php
} // End of POST check
?>

<?php
// Functions and image processing code here

// Function to get Category_ID from Category table
function getCategoryID($conn, $categoryName) {
    $stmt = $conn->prepare("SELECT Category_ID FROM category WHERE Category_Name = ?");
    $stmt->bind_param("s", $categoryName);
    $stmt->execute();
    $result = $stmt->get_result();
    $category_id = $result->fetch_assoc()['Category_ID'] ?? false;
    $stmt->close();
    return $category_id;
}

// Check if Imagick is installed and define the conversion function if it is
if (class_exists('Imagick')) {
    function convertToJpeg($inputPath, $outputPath) {
        $imagick = new Imagick($inputPath);
        $imagick->setImageFormat('jpeg');
        $imagick->writeImage($outputPath);
        $imagick->destroy();
    }
}
?>
