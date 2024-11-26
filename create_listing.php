<?php
session_start(); // Start session to access user information

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) 

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'database_connection.php';

// Function to get Category_ID from the Category table
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
                $allowedTypes = [
                    'image/jpeg', // For JPG and JPEG
                    'image/png',  // For PNG
                    'image/gif',  // For GIF
                    'image/webp', // For WebP
                    'image/avif', // For AVIF
                    'image/heic', // For HEIC
                    'image/heif'  // For HEIF
                ];
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
function resizeImage($source, $destination, $width, $height) {
    $imageInfo = getimagesize($source);
    $sourceWidth = $imageInfo[0];
    $sourceHeight = $imageInfo[1];
    $sourceType = $imageInfo[2];

    // Create source image
    switch ($sourceType) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $sourceImage = imagecreatefromgif($source);
            break;
        case IMAGETYPE_WEBP:
            $sourceImage = imagecreatefromwebp($source);
            break;
        case IMAGETYPE_AVIF: // Only supported in PHP 8.1 or later
            if (function_exists('imagecreatefromavif')) {
                $sourceImage = imagecreatefromavif($source);
            } else {
                throw new Exception("AVIF format not supported on this server.");
            }
            break;
        default:
            throw new Exception("Unsupported image type.");
    }

    // Create resized image
    $resizedImage = imagecreatetruecolor($width, $height);
    imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

    // Save resized image
    switch ($sourceType) {
        case IMAGETYPE_JPEG:
            imagejpeg($resizedImage, $destination);
            break;
        case IMAGETYPE_PNG:
            imagepng($resizedImage, $destination);
            break;
        case IMAGETYPE_GIF:
            imagegif($resizedImage, $destination);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($resizedImage, $destination);
            break;
        case IMAGETYPE_AVIF: // Only supported in PHP 8.1 or later
            if (function_exists('imageavif')) {
                imageavif($resizedImage, $destination);
            } else {
                throw new Exception("AVIF format not supported on this server.");
            }
            break;
    }

    // Free memory
    imagedestroy($sourceImage);
    imagedestroy($resizedImage);
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
    <div class="edit-listing-container">
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
                <div class="listing-form-group">
                <select id="state" name="state" onchange="updateCities()" required>
                <option value="AL" <?= $state === "AL" ? "selected" : ""; ?>>Alabama</option>
<option value="AK" <?= $state === "AK" ? "selected" : ""; ?>>Alaska</option>
<option value="AZ" <?= $state === "AZ" ? "selected" : ""; ?>>Arizona</option>
<option value="AR" <?= $state === "AR" ? "selected" : ""; ?>>Arkansas</option>
<option value="CA" <?= $state === "CA" ? "selected" : ""; ?>>California</option>
<option value="CO" <?= $state === "CO" ? "selected" : ""; ?>>Colorado</option>
<option value="CT" <?= $state === "CT" ? "selected" : ""; ?>>Connecticut</option>
<option value="DE" <?= $state === "DE" ? "selected" : ""; ?>>Delaware</option>
<option value="FL" <?= $state === "FL" ? "selected" : ""; ?>>Florida</option>
<option value="GA" <?= $state === "GA" ? "selected" : ""; ?>>Georgia</option>
<option value="HI" <?= $state === "HI" ? "selected" : ""; ?>>Hawaii</option>
<option value="ID" <?= $state === "ID" ? "selected" : ""; ?>>Idaho</option>
<option value="IL" <?= $state === "IL" ? "selected" : ""; ?>>Illinois</option>
<option value="IN" <?= $state === "IN" ? "selected" : ""; ?>>Indiana</option>
<option value="IA" <?= $state === "IA" ? "selected" : ""; ?>>Iowa</option>
<option value="KS" <?= $state === "KS" ? "selected" : ""; ?>>Kansas</option>
<option value="KY" <?= $state === "KY" ? "selected" : ""; ?>>Kentucky</option>
<option value="LA" <?= $state === "LA" ? "selected" : ""; ?>>Louisiana</option>
<option value="ME" <?= $state === "ME" ? "selected" : ""; ?>>Maine</option>
<option value="MD" <?= $state === "MD" ? "selected" : ""; ?>>Maryland</option>
<option value="MA" <?= $state === "MA" ? "selected" : ""; ?>>Massachusetts</option>
<option value="MI" <?= $state === "MI" ? "selected" : ""; ?>>Michigan</option>
<option value="MN" <?= $state === "MN" ? "selected" : ""; ?>>Minnesota</option>
<option value="MS" <?= $state === "MS" ? "selected" : ""; ?>>Mississippi</option>
<option value="MO" <?= $state === "MO" ? "selected" : ""; ?>>Missouri</option>
<option value="MT" <?= $state === "MT" ? "selected" : ""; ?>>Montana</option>
<option value="NE" <?= $state === "NE" ? "selected" : ""; ?>>Nebraska</option>
<option value="NV" <?= $state === "NV" ? "selected" : ""; ?>>Nevada</option>
<option value="NH" <?= $state === "NH" ? "selected" : ""; ?>>New Hampshire</option>
<option value="NJ" <?= $state === "NJ" ? "selected" : ""; ?>>New Jersey</option>
<option value="NM" <?= $state === "NM" ? "selected" : ""; ?>>New Mexico</option>
<option value="NY" <?= $state === "NY" ? "selected" : ""; ?>>New York</option>
<option value="NC" <?= $state === "NC" ? "selected" : ""; ?>>North Carolina</option>
<option value="ND" <?= $state === "ND" ? "selected" : ""; ?>>North Dakota</option>
<option value="OH" <?= $state === "OH" ? "selected" : ""; ?>>Ohio</option>
<option value="OK" <?= $state === "OK" ? "selected" : ""; ?>>Oklahoma</option>
<option value="OR" <?= $state === "OR" ? "selected" : ""; ?>>Oregon</option>
<option value="PA" <?= $state === "PA" ? "selected" : ""; ?>>Pennsylvania</option>
<option value="RI" <?= $state === "RI" ? "selected" : ""; ?>>Rhode Island</option>
<option value="SC" <?= $state === "SC" ? "selected" : ""; ?>>South Carolina</option>
<option value="SD" <?= $state === "SD" ? "selected" : ""; ?>>South Dakota</option>
<option value="TN" <?= $state === "TN" ? "selected" : ""; ?>>Tennessee</option>
<option value="TX" <?= $state === "TX" ? "selected" : ""; ?>>Texas</option>
<option value="UT" <?= $state === "UT" ? "selected" : ""; ?>>Utah</option>
<option value="VT" <?= $state === "VT" ? "selected" : ""; ?>>Vermont</option>
<option value="VA" <?= $state === "VA" ? "selected" : ""; ?>>Virginia</option>
<option value="WA" <?= $state === "WA" ? "selected" : ""; ?>>Washington</option>
<option value="WV" <?= $state === "WV" ? "selected" : ""; ?>>West Virginia</option>
<option value="WI" <?= $state === "WI" ? "selected" : ""; ?>>Wisconsin</option>
<option value="WY" <?= $state === "WY" ? "selected" : ""; ?>>Wyoming</option>

                </select>

                <div class="listing-city-group">
                    <select id="city-dropdown" name="city" required>
                        <option value="<?= htmlspecialchars($city); ?>" selected><?= htmlspecialchars($city); ?></option>
                    </select>
                </div>

    </div>
</div>
<div id="imagePreviewContainer" class="image-preview-container"></div> <!-- Image Previews -->

<!-- Confirmation Modal -->
<div id="removeImageModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Remove Image</h2>
        <p>Are you sure you want to remove this image from the listing?</p>
        <div class="modal-actions">
            <button id="confirmRemoveImage" class="btn btn-danger">Remove</button>
            <button id="cancelRemoveImage" class="btn">Cancel</button>
        </div>
    </div>
</div>

<input type="hidden" id="thumbnailInput" name="thumbnail" value=""> <!-- Thumbnail designation -->
<input type="hidden" id="removedImagesInput" name="removedImages" value=""> <!-- Removed images -->


            <div class="file-upload-container">
    <!-- File upload button with unique class -->
    <button type="button" class="file-upload-button choose-files-button" onclick="document.getElementById('images').click();">Choose Files</button>
    
    <!-- Hidden file input -->
    <input type="file" 
           id="images" 
           name="images[]" 
           class="file-input" 
           accept=".jpg, .jpeg, .png, .gif, .webp, .avif, .heic, .heif" 
           multiple hidden>
    
    <!-- Optional text feedback -->
    <span class="file-upload-text" id="file-upload-text"></span>
</div>
<div class="btn-container">
    <!-- Update button with unique class -->
    <button type="submit" class="update-button">Create</button>
</div>

            </div>
    </div>
    </div>
    </form>
    </div>
    </div>
    </form>

    <script>
        // Data structure with states and corresponding cities
        const statesAndCities = {
        "Alabama": ["Birmingham", "Montgomery", "Mobile", "Huntsville", "Tuscaloosa"],
        "Alaska": ["Anchorage", "Fairbanks", "Juneau", "Sitka", "Ketchikan"],
        "Arizona": ["Phoenix", "Tucson", "Mesa", "Chandler", "Glendale"],
        "Arkansas": ["Little Rock", "Fort Smith", "Fayetteville", "Springdale", "Jonesboro"],
        "California": ["Los Angeles", "San Diego", "San Jose", "San Francisco", "Fresno"],
        "Colorado": ["Denver", "Colorado Springs", "Aurora", "Fort Collins", "Lakewood"],
        "Connecticut": ["Bridgeport", "New Haven", "Stamford", "Hartford", "Waterbury"],
        "Delaware": ["Wilmington", "Dover", "Newark", "Middletown", "Smyrna"],
        "Florida": ["Jacksonville", "Miami", "Tampa", "Orlando", "St. Petersburg"],
        "Georgia": ["Atlanta", "Augusta", "Columbus", "Macon", "Savannah"],
        "Hawaii": ["Honolulu", "Hilo", "Kailua", "Kapolei", "Kaneohe"],
        "Idaho": ["Boise", "Meridian", "Nampa", "Idaho Falls", "Pocatello"],
        "Illinois": ["Chicago", "Aurora", "Naperville", "Joliet", "Rockford"],
        "Indiana": ["Indianapolis", "Fort Wayne", "Evansville", "South Bend", "Carmel"],
        "Iowa": ["Des Moines", "Cedar Rapids", "Davenport", "Sioux City", "Iowa City"],
        "Kansas": ["Wichita", "Overland Park", "Kansas City", "Olathe", "Topeka"],
        "Kentucky": ["Louisville", "Lexington", "Bowling Green", "Owensboro", "Covington"],
        "Louisiana": ["New Orleans", "Baton Rouge", "Shreveport", "Lafayette", "Lake Charles"],
        "Maine": ["Portland", "Lewiston", "Bangor", "South Portland", "Auburn"],
        "Maryland": ["Baltimore", "Frederick", "Rockville", "Gaithersburg", "Bowie"],
        "Massachusetts": ["Boston", "Worcester", "Springfield", "Lowell", "Cambridge"],
        "Michigan": ["Detroit", "Grand Rapids", "Warren", "Sterling Heights", "Ann Arbor"],
        "Minnesota": ["Minneapolis", "Saint Paul", "Rochester", "Duluth", "Bloomington"],
        "Mississippi": ["Jackson", "Gulfport", "Southaven", "Hattiesburg", "Biloxi"],
        "Missouri": ["Kansas City", "St. Louis", "Springfield", "Columbia", "Independence"],
        "Montana": ["Billings", "Missoula", "Great Falls", "Bozeman", "Butte"],
        "Nebraska": ["Omaha", "Lincoln", "Bellevue", "Grand Island", "Kearney"],
        "Nevada": ["Las Vegas", "Henderson", "Reno", "North Las Vegas", "Sparks"],
        "New Hampshire": ["Manchester", "Nashua", "Concord", "Derry", "Dover"],
        "New Jersey": ["Newark", "Jersey City", "Paterson", "Elizabeth", "Edison"],
        "New Mexico": ["Albuquerque", "Las Cruces", "Rio Rancho", "Santa Fe", "Roswell"],
        "New York": ["New York City", "Buffalo", "Rochester", "Yonkers", "Syracuse"],
        "North Carolina": ["Charlotte", "Raleigh", "Greensboro", "Durham", "Winston-Salem"],
        "North Dakota": ["Fargo", "Bismarck", "Grand Forks", "Minot", "West Fargo"],
        "Ohio": ["Columbus", "Cleveland", "Cincinnati", "Toledo", "Akron"],
        "Oklahoma": ["Oklahoma City", "Tulsa", "Norman", "Broken Arrow", "Lawton"],
        "Oregon": ["Portland", "Salem", "Eugene", "Gresham", "Hillsboro"],
        "Pennsylvania": ["Philadelphia", "Pittsburgh", "Allentown", "Erie", "Reading"],
        "Rhode Island": ["Providence", "Warwick", "Cranston", "Pawtucket", "East Providence"],
        "South Carolina": ["Charleston", "Columbia", "North Charleston", "Mount Pleasant", "Rock Hill"],
        "South Dakota": ["Sioux Falls", "Rapid City", "Aberdeen", "Brookings", "Watertown"],
        "Tennessee": ["Memphis", "Nashville", "Knoxville", "Chattanooga", "Clarksville"],
        "Texas": ["Houston", "San Antonio", "Dallas", "Austin", "Fort Worth"],
        "Utah": ["Salt Lake City", "West Valley City", "Provo", "West Jordan", "Orem"],
        "Vermont": ["Burlington", "South Burlington", "Rutland", "Barre", "Montpelier"],
        "Virginia": ["Virginia Beach", "Norfolk", "Chesapeake", "Richmond", "Newport News"],
        "Washington": ["Seattle", "Spokane", "Tacoma", "Vancouver", "Bellevue"],
        "West Virginia": ["Charleston", "Huntington", "Morgantown", "Parkersburg", "Wheeling"],
        "Wisconsin": ["Milwaukee", "Madison", "Green Bay", "Kenosha", "Racine"],
        "Wyoming": ["Cheyenne", "Casper", "Laramie", "Gillette", "Rock Springs"]
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
        document.addEventListener("DOMContentLoaded", function () {
            console.log("JavaScript loaded, ready to update cities.");
        });
       

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

        document.addEventListener("DOMContentLoaded", function () {
    const imageInput = document.querySelector("input[name='images[]']");
    const previewContainer = document.getElementById("imagePreviewContainer");
    const thumbnailInput = document.getElementById("thumbnailInput");
    const removedImagesInput = document.getElementById("removedImagesInput");

    const modal = document.getElementById("removeImageModal");
    const confirmRemoveButton = document.getElementById("confirmRemoveImage");
    const cancelRemoveButton = document.getElementById("cancelRemoveImage");
    let imageToRemove = null; // Track image to be removed

    imageInput.addEventListener("change", function () {
        previewContainer.innerHTML = ""; // Clear previous previews
        Array.from(this.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const imgWrapper = document.createElement("div");
                imgWrapper.classList.add("image-wrapper");

                const img = document.createElement("img");
                img.src = e.target.result;
                img.classList.add("preview-image");
                img.dataset.fileName = file.name; // Use filename to track images

                // Left-click: Designate as thumbnail
                img.addEventListener("click", function () {
                    // Clear previous thumbnail designation
                    const allImages = previewContainer.querySelectorAll(".preview-image");
                    allImages.forEach(image => image.classList.remove("thumbnail"));
                    
                    // Highlight as thumbnail
                    this.classList.add("thumbnail");
                    thumbnailInput.value = file.name; // Set thumbnail input value
                });

                // Right-click: Remove image
                img.addEventListener("contextmenu", function (event) {
                    event.preventDefault();
                    imageToRemove = imgWrapper; // Track the wrapper for removal
                    modal.style.display = "flex"; // Show modal
                });

                imgWrapper.appendChild(img);
                previewContainer.appendChild(imgWrapper);
            };
            reader.readAsDataURL(file);
        });
    });

    // Confirm image removal
    confirmRemoveButton.addEventListener("click", function () {
        if (imageToRemove) {
            const fileName = imageToRemove.querySelector("img").dataset.fileName;
            // Add the file to removed images list
            const removedImages = removedImagesInput.value ? removedImagesInput.value.split(",") : [];
            removedImages.push(fileName);
            removedImagesInput.value = removedImages.join(",");

            // Remove from preview
            previewContainer.removeChild(imageToRemove);
        }
        modal.style.display = "none"; // Hide modal
    });

    // Cancel image removal
    cancelRemoveButton.addEventListener("click", function () {
        modal.style.display = "none"; // Hide modal
        imageToRemove = null; // Reset tracked image
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

        .choose-files-button,
.update-button {
    min-width: 150px; /* Minimum button width */
    text-align: center;
    padding: 10px 15px;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    font-size: 16px;
}

.choose-files-button {
    background-color: #007bff; /* Blue */
    color: #fff;
}

.choose-files-button:hover {
    background-color: #0056b3; /* Darker blue */
}

.update-button {
    background-color: #28a745; /* Green */
    color: #fff;
}

.update-button:hover {
    background-color: #218838; /* Darker green */
}

    </style>


</body>

<?php include 'footer.php'; ?>

</html>