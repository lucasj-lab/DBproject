<?php
session_start();
require 'database_connection.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to create a listing.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user details from session
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $state = trim($_POST['state']);
    $city = trim($_POST['city']) ?: trim($_POST['city-input']);
    $category = $_POST['category'];

    // Check if all fields are filled
    if (empty($title) || empty($description) || empty($price) || empty($state) || empty($city)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit();
    }

    // Get Category_ID
    $category_id = getCategoryID($conn, $category);
    if ($category_id === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid category selected.']);
        exit();
    }

    // Insert new listing into database
    $stmt = $conn->prepare("INSERT INTO listings (User_ID, Title, Description, Price, Date_Posted, Category_ID, State, City) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)");
    $stmt->bind_param("issdiss", $user_id, $title, $description, $price, $category_id, $state, $city);

    if ($stmt->execute()) {
        $listing_id = $stmt->insert_id; // Get the ID of the new listing

        // Check if images were uploaded
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDirectory = 'uploads/';
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                $originalFileName = basename($_FILES['images']['name'][$key]);
                $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
                $uniqueImageName = uniqid() . '_' . pathinfo($originalFileName, PATHINFO_FILENAME);

                // Check for HEIC or HEIF format and convert if needed
                if (($fileExtension === 'heic' || $fileExtension === 'heif') && class_exists('Imagick')) {
                    $convertedFilePath = $uploadDirectory . $uniqueImageName . '.jpg';
                    convertToJpeg($tmpName, $convertedFilePath);
                    $imageUrl = $convertedFilePath;
                } else {
                    // Save original format
                    $targetFilePath = $uploadDirectory . $uniqueImageName . '.' . $fileExtension;
                    if (move_uploaded_file($tmpName, $targetFilePath)) {
                        $imageUrl = $targetFilePath;
                    }
                }

                // Insert image data into the database
                if (isset($imageUrl)) {
                    $imageSql = "INSERT INTO images (image_url, listing_id) VALUES (?, ?)";
                    $imgStmt = $conn->prepare($imageSql);
                    $imgStmt->bind_param("si", $imageUrl, $listing_id);
                    $imgStmt->execute();
                    $imgStmt->close();
                }
            }
        }

        echo json_encode(['success' => true, 'message' => 'Listing created successfully! <a href=\'account.php\'> Click here to view your listings.</a>']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: Unable to create listing.']);
    }

    $stmt->close();
}

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
} else {
    echo "Warning: Imagick is not installed, HEIC/HEIF images may not be supported.";
}

$conn->close();
?>
