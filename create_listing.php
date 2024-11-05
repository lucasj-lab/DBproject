<?php
session_start();
require 'database_connection.php';

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

    if (empty($title) || empty($description) || empty($price) || empty($state) || empty($city)) {
        echo "All fields are required.";
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO listings (User_ID, Title, Description, Price, State, City) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdss", $user_id, $title, $description, $price, $state, $city);

    if ($stmt->execute()) {
        echo "Success! Your listing has been created.";
    } else {
        echo "Error: " . $stmt->error;
    }
}

    $stmt->close();
    $conn->close();

// Function to get Category_ID from Category table
function getCategoryID($conn, $categoryName) {
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_email = $_POST['user_email'];
    $category = $_POST['category'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $state = $_POST['state'];
    $city = $_POST['city'] ?? $_POST['city-input'];

    // Get the User_ID using the email
    $stmt = $conn->prepare("SELECT User_ID FROM user WHERE Email = ?");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id);
        $stmt->fetch();

        // Get the Category_ID using the function
        $category_id = getCategoryID($conn, $category);

        if ($category_id === false) {
            echo json_encode(['success' => false, 'message' => 'Invalid category selected.']);
        } else {
            // Insert new listing
            $stmt = $conn->prepare("INSERT INTO listings (Title, Description, Price, Date_Posted, User_ID, Category_ID, State, City) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)");
            $stmt->bind_param("ssissss", $title, $description, $price, $user_id, $category_id, $state, $city);
            
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
                        }
                    }
                }

                // Return success message
                echo json_encode(['success' => true, 'message' => 'Listing created successfully! <a href=\'account.php\'> Click here to view your listings.</a>']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error: Unable to create listing.']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User email not found.']);
    }

    $stmt->close();
}
