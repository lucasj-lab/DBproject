<?php
session_start();
require 'database_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve POST data from the form submission
$listing_id = $_POST['listing_id'];
$title = $_POST['title'];
$description = $_POST['description'];
$price = $_POST['price'];
$state = $_POST['state'];
$city = $_POST['city'];
$user_id = $_SESSION['user_id'];

// Ensure that required fields are not empty
if (empty($title) || empty($description) || empty($price) || empty($state) || empty($city)) {
    echo "All fields are required!";
    exit();
}

// Update the listing information in the database
$sql = "UPDATE listings 
        SET title = :title, description = :description, price = :price, state = :state, city = :city
        WHERE Listing_ID = :listing_id AND user_id = :user_id";
$stmt = $pdo->prepare($sql);
$success = $stmt->execute([
    'title' => $title,
    'description' => $description,
    'price' => $price,
    'state' => $state,
    'city' => $city,
    'listing_id' => $listing_id,
    'user_id' => $user_id
]);

// Check if the update was successful
if (!$success) {
    echo "Failed to update the listing.";
    exit();
}

// Handle new image upload if a file is provided
if (!empty($_FILES['new_image']['name'][0])) {
    foreach ($_FILES['new_image']['tmp_name'] as $key => $tmpName) {
        $imageName = basename($_FILES['new_image']['name'][$key]);
        $targetFilePath = 'uploads/' . time() . "_" . $imageName;

        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($tmpName, $targetFilePath)) {
            // Insert the image URL into the images table for this listing
            $sql = "INSERT INTO images (Listing_ID, image_url) VALUES (:listing_id, :image_url)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['listing_id' => $listing_id, 'image_url' => $targetFilePath]);
        } else {
            echo "Image upload failed.";
            exit();
        }
    }
}

// Redirect back to a confirmation or view page for the updated listing
header("Location: view_listing.php?listing_id=" . $listing_id);
exit();
?>
