<?php
session_start();
require 'database_connection.php';
include 'header.php';

// Ensure the user is logged in and has access to this listing
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the listing ID from the query string
$listing_id = $_GET['listing_id'];
$user_id = $_SESSION['user_id'];

// Fetch listing details and associated images
$sql = "SELECT listings.title, listings.description, listings.price, listings.city, listings.state, images.image_url 
        FROM listings 
        LEFT JOIN images ON listings.Listing_ID = images.Listing_ID 
        WHERE listings.Listing_ID = :listing_id AND listings.user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['listing_id' => $listing_id, 'user_id' => $user_id]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$listing) {
    echo "Listing not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Listing</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <form id="create-listing-form">
    <h1 class="edit-listing-title">Edit Your Listing</h1>

    <form action="update_listing.php" method="POST" enctype="multipart/form-data" class="edit-listing-container">
        <!-- Hidden input to keep the listing ID -->
        <input type="hidden" name="listing_id" value="<?php echo htmlspecialchars($listing_id); ?>">

        <label for="title">Title:</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($listing['title']); ?>" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description"
            required><?php echo htmlspecialchars($listing['description']); ?></textarea>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($listing['price']); ?>"
            required>

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