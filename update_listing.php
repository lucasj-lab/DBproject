<?php
session_start();
require 'database_connection.php';

// Check if user is logged in
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

// Handle image upload if a new image was uploaded
if (!empty($_FILES['new_image']['name'])) {
    $image_path = 'uploads/' . basename($_FILES['new_image']['name']);

    // Move the uploaded file to the uploads directory
    if (move_uploaded_file($_FILES['new_image']['tmp_name'], $image_path)) {
        // Update the image URL in the images table, or insert it if it doesn’t exist
        $sql = "INSERT INTO images (Listing_ID, image_url) VALUES (:listing_id, :image_url) 
                ON DUPLICATE KEY UPDATE image_url = :image_url";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['listing_id' => $listing_id, 'image_url' => $image_path]);
    } else {
        echo "Image upload failed.";
        exit();
    }
}

// Redirect back to a confirmation or view page for the updated listing
header("Location: view_listing.php?listing_id=" . $listing_id);
exit();
?>
