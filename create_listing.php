<?php
session_start();
require 'database_connection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$uploadDir = '/var/www/html/uploads/';

if (!isset($_SESSION['user_id'])) {
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Not Logged In</title>
        <link rel='stylesheet' href='styles.css'>
    </head>
    <body>
        <div class='redirect-message'>
            <h2>You must be logged in to create a listing.</h2>
            <p>Please <a href='login.php'>log in</a> or <a href='signup.php'>sign up</a> to continue.</p>
        </div>
    </body>
    </html>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $state = trim($_POST['state']);
    $city = isset($_POST['city']) && trim($_POST['city']) !== '' ? trim($_POST['city']) : (isset($_POST['city-input']) ? trim($_POST['city-input']) : '');

    $category = ucfirst(strtolower(trim($_POST['category'])));

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

    // Insert new listing into listings table
    $stmt = $conn->prepare("INSERT INTO listings (User_ID, Title, Description, Price, Date_Posted, Category_ID, State, City) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)");
    $stmt->bind_param("issdiss", $user_id, $title, $description, $price, $category_id, $state, $city);

    if ($stmt->execute()) {
        $listing_id = $stmt->insert_id;
        echo "<div class='alert alert-success'>Listing created successfully! <a href='my_listings.php' class='pill-button'>View your listings</a></div>";

        // Handle multiple image uploads and insert into images table
        if (!empty($_FILES['images']['name'][0])) {
            $image_stmt = $conn->prepare("INSERT INTO images (Image_URL, Listing_ID) VALUES (?, ?)");

            foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
                $fileName = basename($_FILES['images']['name'][$index]);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    $image_url = 'uploads/' . $fileName;
                    $image_stmt->bind_param("si", $image_url, $listing_id);
                    $image_stmt->execute();
                    echo "<p>Image $fileName uploaded successfully and saved in the database.</p>";
                } else {
                    echo "<p>Error uploading $fileName.</p>";
                }
            }
            $image_stmt->close();
        }
    } else {
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
    <div class="post-ad">
        <h2>Post Your Ad</h2>
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
