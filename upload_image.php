<?php 
header('Content-Type: application/json');

// Directory where uploaded images will be stored
$targetDirectory = "uploads/";

// Ensure the directory exists
if (!is_dir($targetDirectory)) {
    mkdir($targetDirectory, 0755, true);
}

// Check if a file was uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $image = $_FILES['image'];

 // Validate file type
 $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'heic/heif'];
 if (!in_array($image['type'], $allowedTypes)) {
     echo json_encode(["error" => "Only JPG, PNG, GIF, WebP, AVIF, and HEIC/HEIF formats are allowed."]);
     exit();
 }
    // Generate a unique file name to prevent overwriting
    $fileName = uniqid() . "-" . basename($image['name']);
    $targetFilePath = $targetDirectory . $fileName;

    // Move the file to the target directory
    if (move_uploaded_file($image['tmp_name'], $targetFilePath)) {
        echo json_encode(["success" => true, "filePath" => $targetFilePath]);
    } else {
        echo json_encode(["error" => "Failed to upload image."]);
    }
    exit();
}

echo json_encode(["error" => "Invalid request."]);
exit();
