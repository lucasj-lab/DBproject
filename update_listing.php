<?php
session_start();
require 'database_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "You must be logged in to perform this action.";
    $_SESSION['message_type'] = "error";
    header("Location: login.php");
    exit();
}

// Retrieve POST data from the form submission
$listing_id = $_POST['listing_id'] ?? null;
$title = $_POST['title'] ?? null;
$description = $_POST['description'] ?? null;
$price = $_POST['price'] ?? null;
$state = $_POST['state'] ?? null;
$city = $_POST['city'] ?? null;
$selected_thumbnail = $_POST['selected_thumbnail'] ?? null; // Get the selected thumbnail
$user_id = $_SESSION['user_id'];

// Check if the listing ID is provided
if (!$listing_id) {
    $_SESSION['message'] = "No listing ID provided.";
    $_SESSION['message_type'] = "error";
    header("Location: user_dashboard.php");
    exit();
}

// Validate required fields
if (empty($title) || empty($description) || empty($price) || empty($state) || empty($city)) {
    $_SESSION['message'] = "All fields are required.";
    $_SESSION['message_type'] = "error";
    header("Location: edit_listing.php?listing_id=$listing_id");
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Update the listing with the provided details
    $sql = "
        UPDATE listings 
        SET Title = :title, Description = :description, Price = :price, State = :state, City = :city, Thumbnail_Image = :thumbnail
        WHERE Listing_ID = :listing_id AND User_ID = :user_id
    ";
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([
        'title' => $title,
        'description' => $description,
        'price' => $price,
        'state' => $state,
        'city' => $city,
        'thumbnail' => $selected_thumbnail, // Update the thumbnail
        'listing_id' => $listing_id,
        'user_id' => $user_id
    ]);

    if (!$success) {
        throw new Exception("Failed to update the listing details.");
    }

    // Handle new image uploads if provided
    if (!empty($_FILES['new_image']['name'][0])) {
        foreach ($_FILES['new_image']['tmp_name'] as $key => $tmpName) {
            $imageName = basename($_FILES['new_image']['name'][$key]);
            $targetFilePath = 'uploads/' . time() . "_" . $imageName;

            // Move the uploaded file to the uploads directory
            if (move_uploaded_file($tmpName, $targetFilePath)) {
                // Insert the image URL into the images table for this listing
                $sql = "INSERT INTO images (Listing_ID, Image_URL) VALUES (:listing_id, :image_url)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['listing_id' => $listing_id, 'image_url' => $targetFilePath]);
            } else {
                throw new Exception("Failed to upload one or more images.");
            }
        }
    }

    // Commit transaction
    $pdo->commit();

    // Success message and redirect
    $_SESSION['message'] = "Listing updated successfully.";
    $_SESSION['message_type'] = "success";
    header("Location: listing_success.php?listing_id=$listing_id");
    exit();
} catch (Exception $e) {
    // Rollback transaction on failure
    $pdo->rollBack();

    // Log error and redirect with an error message
    error_log("Error updating listing: " . $e->getMessage());
    $_SESSION['message'] = "Failed to update the listing: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    header("Location: edit_listing.php?listing_id=$listing_id");
    exit();
}
?>
