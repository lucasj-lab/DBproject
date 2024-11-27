<?php
session_start();
require 'database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['listing_id'])) {
    die("No listing ID provided.");
}

$listing_id = intval($_GET['listing_id']);
$user_id = $_SESSION['user_id'];

// Fetch listing details
$stmt = $conn->prepare("
    SELECT Title, Description, Price, State, City 
    FROM listings 
    WHERE Listing_ID = ? AND User_ID = ?
");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$stmt->bind_result($title, $description, $price, $state, $city);

if (!$stmt->fetch()) {
    die("Listing not found or you do not have permission to edit this listing.");
}
$stmt->close();

// Fetch images for the listing
$imageStmt = $conn->prepare("
    SELECT Image_ID, Image_URL, Is_Thumbnail 
    FROM images 
    WHERE Listing_ID = ?
");
$imageStmt->bind_param("i", $listing_id);
$imageStmt->execute();
$result = $imageStmt->get_result();
$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}
$imageStmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0.0;
    $state = $_POST['state'] ?? '';
    $city = $_POST['city'] ?? '';
    $selected_thumbnail = $_POST['selected_thumbnail'] ?? null;

    // Update listing details
    $updateStmt = $conn->prepare("
        UPDATE listings 
        SET Title = ?, Description = ?, Price = ?, State = ?, City = ? 
        WHERE Listing_ID = ? AND User_ID = ?
    ");
    $updateStmt->bind_param("ssdssii", $title, $description, $price, $state, $city, $listing_id, $user_id);
    $updateStmt->execute();
    $updateStmt->close();

    // Update thumbnail selection
    if ($selected_thumbnail) {
        $resetThumbnailStmt = $conn->prepare("UPDATE images SET Is_Thumbnail = 0 WHERE Listing_ID = ?");
        $resetThumbnailStmt->bind_param("i", $listing_id);
        $resetThumbnailStmt->execute();

        $setThumbnailStmt = $conn->prepare("UPDATE images SET Is_Thumbnail = 1 WHERE Image_ID = ? AND Listing_ID = ?");
        $setThumbnailStmt->bind_param("ii", $selected_thumbnail, $listing_id);
        $setThumbnailStmt->execute();
    }

    // Handle new image uploads
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDirectory = 'uploads/';
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            $fileType = mime_content_type($tmpName);
            $allowedTypes = [
                'image/jpeg', // For JPG and JPEG
                'image/png',  // For PNG
                'image/gif',  // For GIF
                'image/webp', // For WebP
                'image/avif', // For AVIF
                'image/heic', // For HEIC
                'image/heif'  // For HEIF
            ];
            if (in_array($fileType, $allowedTypes)) {
                $imageName = basename($_FILES['images']['name'][$key]);
                $uniqueImageName = time() . "_" . $imageName;
                $targetFilePath = $uploadDirectory . $uniqueImageName;

                if (move_uploaded_file($tmpName, $targetFilePath)) {
                    $isThumbnail = ($key === 0 && empty($selected_thumbnail)) ? 1 : 0;
                    $imageUrl = $targetFilePath;

                    $imageStmt = $conn->prepare("INSERT INTO images (Listing_ID, Image_URL, Is_Thumbnail) VALUES (?, ?, ?)");
                    $imageStmt->bind_param("isi", $listing_id, $imageUrl, $isThumbnail);
                    $imageStmt->execute();
                }
            }
        }
    }

    // Redirect after successful update
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Listing Updated Successfully</title>
        <link rel='stylesheet' href='styles.css'>
    </head>
    <body>
        <div class='success-message-container'>
            <div class='success-message'>
                <h2>Your listing has been successfully updated!</h2>
                <p>You can take the following actions:</p>
                <div class='navigation-options'>
                    <a href='edit_listing.php?listing_id=" . htmlspecialchars($listing_id) . "' class='pill-button'>Edit This Listing Again</a>
                    <a href='create_listing.php' class='pill-button'>Create Another Listing</a>
                    <a href='listings.php' class='pill-button'>Browse All Listings</a>
                    <a href='user_dashboard.php' class='pill-button'>Go to Dashboard</a>
                </div>
            </div>
        </div>
    </body>
    </html>";
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Listing</title>
<link rel="stylesheet" href="styles.css">
<script src="dynamic_cities.js"></script>
<style>
    /* Main Thumbnail Section */
    .main-image-container {
        text-align: center;
        margin-bottom: 20px;
    }

    .main-image {
        width: 300px;
        border: 2px solid #000;
        border-radius: 5px;
    }

    /* Image Gallery */
    .image-gallery {
        display: flex;
        overflow-y: hidden;
        gap: 10px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f9f9f9;
        max-width: 100%;
        /* Ensure it stays within the container */
        box-sizing: border-box;
        white-space: nowrap;
        /* Prevent wrapping of images */
    }

    .image-item {
        flex-shrink: 0;
        /* Prevent images from shrinking */
        text-align: center;
        position: relative;
    }

    .image-item img {
        max-width: 100px;
        max-height: 100px;
        object-fit: cover;
        border: 1px solid #ddd;
        border-radius: 5px;
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .image-item img:hover {
        transform: scale(1.1);
        border-color: lightgray;
    }

    .thumbnail-radio {
        display: block;
        margin-top: 5px;
        cursor: pointer;
    }

    /* Scrollbar styling */
    .image-gallery::-webkit-scrollbar {
        height: 8px;
        /* Scrollbar height */
    }

    .image-gallery::-webkit-scrollbar-thumb {
        background: #ccc;
        /* Scrollbar thumb color */
        border-radius: 4px;
        /* Rounded scrollbar */
    }

    .image-gallery::-webkit-scrollbar-thumb:hover {
        background: #aaa;
        /* Darker thumb on hover */
    }

    .image-gallery::-webkit-scrollbar-track {
        background: #f9f9f9;
        /* Scrollbar track color */
    }

    .choose-files-button,
    .update-button {
        min-width: 150px;
        /* Minimum button width */
        text-align: center;
        padding: 10px 15px;
        border: none;
        border-radius: 30px;
        cursor: pointer;
        font-size: 16px;
    }

    .choose-files-button {
        background-color: #007bff;
        /* Blue */
        color: #fff;
    }

    .choose-files-button:hover {
        background-color: #0056b3;
        /* Darker blue */
    }

    .update-button {
        background-color: #28a745;
        /* Green */
        color: #fff;
    }

    .update-button:hover {
        background-color: #218838;
        /* Darker green */
    }
</style>
<script>

    const statesAndCities = {
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
    };

    cityDropdown.innerHTML = '<option value="">--Select City--</option>';
    if (selectedState && statesAndCities[selectedState]) {
        statesAndCities[selectedState].forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            cityDropdown.appendChild(option);
        });
    }


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
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="edit-listing-container">
        <h1 class="edit-listing-title">Edit Listing</h1>
        <form id="create-listing-form" method="POST" enctype="multipart/form-data">
            <div class="listing-form-group">
                <input type="text" id="title" name="title" placeholder="Title" value="<?= htmlspecialchars($title); ?>"
                    required>
                <textarea id="description" name="description" rows="4" placeholder="Description"
                    required><?= htmlspecialchars($description); ?></textarea>
                <input type="number" step="0.01" id="price" name="price" placeholder="Price"
                    value="<?= htmlspecialchars($price); ?>" required>

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

                <select id="city-dropdown" name="city" required>
                    <option value="<?= htmlspecialchars($city); ?>" selected><?= htmlspecialchars($city); ?></option>
                </select>
                </select>
            </div>

            <!-- Display current images as a gallery -->
            <div class="image-gallery">
                <?php foreach ($images as $image): ?>
                    <div class="image-item">
                        <img src="<?= htmlspecialchars($image['Image_URL']); ?>" alt="Listing Image">
                        <input type="radio" name="selected_thumbnail" value="<?= $image['Image_ID']; ?>"
                            <?= $image['Is_Thumbnail'] ? "checked" : ""; ?>>

                    </div>
                <?php endforeach; ?>
            </div>

            <div class="file-upload-container">
                <!-- File upload button with unique class -->
                <button type="button" class="file-upload-button choose-files-button"
                    onclick="document.getElementById('images').click();">Choose Files</button>

                <!-- Hidden file input -->
                <input type="file" id="images" name="images[]" class="file-input"
                    accept=".jpg, .jpeg, .png, .gif, .webp, .avif, .heic, .heif" multiple hidden>

                <!-- Optional text feedback -->
                <span class="file-upload-text" id="file-upload-text"></span>
            </div>
            <div class="btn-container">
                <!-- Update button with unique class -->
                <button type="submit" class="update-button">Update</button>
            </div>

    </div>

    </form>
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



    <script src="dynamic_cities.js"></script>

</body>

</html>