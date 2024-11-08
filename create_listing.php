
There are a few issues with the code snippet you've posted:

Duplicate getCategoryID Function: You’ve defined the getCategoryID function twice. You only need one definition of this function, so remove one of them to avoid conflicts.

Placement of Helper Functions: Yes, you can place helper functions like getCategoryID and convertToJpeg at the end of your script if they’re not needed until later in the script execution. However, make sure that these functions are defined before you attempt to call them.

Imagick Processing: The convertToJpeg function is fine at the end as long as it’s called after the function definition. If you’re converting images to JPEG format before saving them, this function could be used within the main code that handles image uploads.

Cleaned-Up Code Structure
Here’s how you can structure the code with only one instance of getCategoryID, along with convertToJpeg defined at the end:

php
Copy code
<?php
session_start();
require 'database_connection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$uploadDir = '/var/www/html/uploads/';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to create a listing.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $state = trim($_POST['state']);
    $city = trim($_POST['city']) ?: trim($_POST['city-input']);
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
            foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
                $fileName = basename($_FILES['images']['name'][$index]);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    // Optional: Convert image to JPEG if needed
                    // Uncomment below if using Imagick to convert images to JPEG format
                    // convertToJpeg($targetPath, $targetPath); 

                    // Store relative path in database
                    $image_url = 'uploads/' . $fileName;
                    $stmt = $conn->prepare("INSERT INTO images (Image_URL, Listing_ID) VALUES (?, ?)");
                    $stmt->bind_param("si", $image_url, $listing_id);
                    $stmt->execute();
                    echo "<p>Image $fileName uploaded successfully and saved in the database.</p>";
                    $stmt->close();
                } else {
                    echo "<p>Error uploading $fileName.</p>";
                }
            }
        }
    } else {
        echo "<div class='alert alert-error'>Database error: Unable to create listing.</div>";
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

<?php
// Function to retrieve Category_ID from the database
function getCategoryID($conn, $categoryName) {
    $stmt = $conn->prepare("SELECT Category_ID FROM category WHERE Category_Name = ?");
    $stmt->bind_param("s", $categoryName);
    $stmt->execute();
    $result = $stmt->get_result();
    $category_id = $result->fetch_assoc()['Category_ID'] ?? false;
    $stmt->close();
    return $category_id;
}

// Optional: Function to convert images to JPEG format using Imagick
if (class_exists('Imagick')) {
    function convertToJpeg($inputPath, $outputPath) {
        $imagick = new Imagick($inputPath);
        $imagick->setImageFormat('jpeg');
        $imagick->writeImage($outputPath);
        $imagick->destroy();
    }
}
?>