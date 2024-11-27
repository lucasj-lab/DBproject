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
function resizeImage($source, $destination, $width, $height)
{
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
    <styles>
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

    </styles>
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
                        <option value="">--Select State--</option>
                        <option value="AL">Alabama</option>
                        <option value="AK">Alaska</option>
                        <option value="AZ">Arizona</option>
                        <option value="AR">Arkansas</option>
                        <option value="CA">California</option>
                        <option value="CO">Colorado</option>
                        <option value="CT">Connecticut</option>
                        <option value="DE">Delaware</option>
                        <option value="FL">Florida</option>
                        <option value="GA">Georgia</option>
                        <option value="HI">Hawaii</option>
                        <option value="ID">Idaho</option>
                        <option value="IL">Illinois</option>
                        <option value="IN">Indiana</option>
                        <option value="IA">Iowa</option>
                        <option value="KS">Kansas</option>
                        <option value="KY">Kentucky</option>
                        <option value="LA">Louisiana</option>
                        <option value="ME">Maine</option>
                        <option value="MD">Maryland</option>
                        <option value="MA">Massachusetts</option>
                        <option value="MI">Michigan</option>
                        <option value="MN">Minnesota</option>
                        <option value="MS">Mississippi</option>
                        <option value="MO">Missouri</option>
                        <option value="MT">Montana</option>
                        <option value="NE">Nebraska</option>
                        <option value="NV">Nevada</option>
                        <option value="NH">New Hampshire</option>
                        <option value="NJ">New Jersey</option>
                        <option value="NM">New Mexico</option>
                        <option value="NY">New York</option>
                        <option value="NC">North Carolina</option>
                        <option value="ND">North Dakota</option>
                        <option value="OH">Ohio</option>
                        <option value="OK">Oklahoma</option>
                        <option value="OR">Oregon</option>
                        <option value="PA">Pennsylvania</option>
                        <option value="RI">Rhode Island</option>
                        <option value="SC">South Carolina</option>
                        <option value="SD">South Dakota</option>
                        <option value="TN">Tennessee</option>
                        <option value="TX">Texas</option>
                        <option value="UT">Utah</option>
                        <option value="VT">Vermont</option>
                        <option value="VA">Virginia</option>
                        <option value="WA">Washington</option>
                        <option value="WV">West Virginia</option>
                        <option value="WI">Wisconsin</option>
                        <option value="WY">Wyoming</option>
                    </select>


                    <select id="city-dropdown" name="city" required>
                        <option value="">--Select City--</option>
                    </select>

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
                <button type="submit" class="update-button">Create</button>
            </div>



<script>
                // List of cities for each state
                const statesAndCities = {
                    "AL": ["Birmingham", "Montgomery", "Mobile", "Huntsville", "Tuscaloosa"],
                    "AK": ["Anchorage", "Fairbanks", "Juneau", "Sitka", "Ketchikan"],
                    "AZ": ["Phoenix", "Tucson", "Mesa", "Chandler", "Glendale"],
                    "AR": ["Little Rock", "Fort Smith", "Fayetteville", "Springdale", "Jonesboro"],
                    "CA": ["Los Angeles", "San Diego", "San Jose", "San Francisco", "Fresno"],
                    "CO": ["Denver", "Colorado Springs", "Aurora", "Fort Collins", "Lakewood"],
                    "CT": ["Bridgeport", "New Haven", "Stamford", "Hartford", "Waterbury"],
                    "DE": ["Wilmington", "Dover", "Newark", "Middletown", "Smyrna"],
                    "FL": ["Jacksonville", "Miami", "Tampa", "Orlando", "St. Petersburg"],
                    "GA": ["Atlanta", "Augusta", "Columbus", "Macon", "Savannah"],
                    "HI": ["Honolulu", "Hilo", "Kailua", "Kapolei", "Kaneohe"],
                    "ID": ["Boise", "Meridian", "Nampa", "Idaho Falls", "Pocatello"],
                    "IL": ["Chicago", "Aurora", "Naperville", "Joliet", "Rockford"],
                    "IN": ["Indianapolis", "Fort Wayne", "Evansville", "South Bend", "Carmel"],
                    "IA": ["Des Moines", "Cedar Rapids", "Davenport", "Sioux City", "Iowa City"],
                    "KS": ["Wichita", "Overland Park", "Kansas City", "Olathe", "Topeka"],
                    "KY": ["Louisville", "Lexington", "Bowling Green", "Owensboro", "Covington"],
                    "LA": ["New Orleans", "Baton Rouge", "Shreveport", "Lafayette", "Lake Charles"],
                    "ME": ["Portland", "Lewiston", "Bangor", "South Portland", "Auburn"],
                    "MD": ["Baltimore", "Frederick", "Rockville", "Gaithersburg", "Bowie"],
                    "MA": ["Boston", "Worcester", "Springfield", "Lowell", "Cambridge"],
                    "MI": ["Detroit", "Grand Rapids", "Warren", "Sterling Heights", "Ann Arbor"],
                    "MN": ["Minneapolis", "Saint Paul", "Rochester", "Duluth", "Bloomington"],
                    "MS": ["Jackson", "Gulfport", "Southaven", "Hattiesburg", "Biloxi"],
                    "MO": ["Kansas City", "St. Louis", "Springfield", "Columbia", "Independence"],
                    "MT": ["Billings", "Missoula", "Great Falls", "Bozeman", "Butte"],
                    "NE": ["Omaha", "Lincoln", "Bellevue", "Grand Island", "Kearney"],
                    "NV": ["Las Vegas", "Henderson", "Reno", "North Las Vegas", "Sparks"],
                    "NH": ["Manchester", "Nashua", "Concord", "Derry", "Dover"],
                    "NJ": ["Newark", "Jersey City", "Paterson", "Elizabeth", "Edison"],
                    "NM": ["Albuquerque", "Las Cruces", "Rio Rancho", "Santa Fe", "Roswell"],
                    "NY": ["New York City", "Buffalo", "Rochester", "Yonkers", "Syracuse"],
                    "NC": ["Charlotte", "Raleigh", "Greensboro", "Durham", "Winston-Salem"],
                    "ND": ["Fargo", "Bismarck", "Grand Forks", "Minot", "West Fargo"],
                    "OH": ["Columbus", "Cleveland", "Cincinnati", "Toledo", "Akron"],
                    "OK": ["Oklahoma City", "Tulsa", "Norman", "Broken Arrow", "Lawton"],
                    "OR": ["Portland", "Salem", "Eugene", "Gresham", "Hillsboro"],
                    "PA": ["Philadelphia", "Pittsburgh", "Allentown", "Erie", "Reading"],
                    "RI": ["Providence", "Warwick", "Cranston", "Pawtucket", "East Providence"],
                    "SC": ["Charleston", "Columbia", "North Charleston", "Mount Pleasant", "Rock Hill"],
                    "SD": ["Sioux Falls", "Rapid City", "Aberdeen", "Brookings", "Watertown"],
                    "TN": ["Memphis", "Nashville", "Knoxville", "Chattanooga", "Clarksville"],
                    "TX": ["Houston", "San Antonio", "Dallas", "Austin", "Fort Worth"],
                    "UT": ["Salt Lake City", "West Valley City", "Provo", "West Jordan", "Orem"],
                    "VT": ["Burlington", "South Burlington", "Rutland", "Barre", "Montpelier"],
                    "VA": ["Virginia Beach", "Norfolk", "Chesapeake", "Richmond", "Newport News"],
                    "WA": ["Seattle", "Spokane", "Tacoma", "Vancouver", "Bellevue"],
                    "WV": ["Charleston", "Huntington", "Morgantown", "Parkersburg", "Wheeling"],
                    "WI": ["Milwaukee", "Madison", "Green Bay", "Kenosha", "Racine"],
                    "WY": ["Cheyenne", "Casper", "Laramie", "Gillette", "Rock Springs"]
                };

                // Function to update city dropdown based on selected state
                function updateCities() {
                    const stateDropdown = document.getElementById("state");
                    const cityDropdown = document.getElementById("city-dropdown");

                    // Clear existing city options
                    cityDropdown.innerHTML = '<option value="">--Select City--</option>';

                    const selectedState = stateDropdown.value;
                    if (selectedState && citiesByState[selectedState]) {
                        citiesByState[selectedState].forEach(city => {
                            const option = document.createElement("option");
                            option.value = city;
                            option.textContent = city;
                            cityDropdown.appendChild(option);
                        });
                    }
                }
            </script>
            </body>
</html>
