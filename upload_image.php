<?php 
header('Content-Type: application/json');

// Directory where uploaded images will be stored
$targetDirectory = "uploads/";

// Ensure the directory exists
if (!is_dir($targetDirectory)) {
    mkdir($targetDirectory, 0755, true);
}
<?php
// Check if a file was uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $image = $_FILES['image'];

    // Allowed MIME types and extensions
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'image/heif', 'image/heic'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'heif', 'heic'];

    // Detect MIME type
    $detectedType = mime_content_type($image['tmp_name']);
    $fileExtension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

    // Debugging (optional): Uncomment to see detected MIME type and extension
    // echo "Detected MIME type: $detectedType, File extension: $fileExtension";
    // exit();

    // Validate MIME type or extension
    if (!in_array($detectedType, $allowedTypes) && !in_array($fileExtension, $allowedExtensions)) {
        echo json_encode(["error" => "File type not allowed: $detectedType or .$fileExtension"]);
        exit();
    }

    // Set target directory (update with your actual directory path)
    $targetDirectory = __DIR__ . '/uploads/'; // Ensure this directory exists and is writable
    if (!is_dir($targetDirectory)) {
        mkdir($targetDirectory, 0777, true); // Create directory if it doesn't exist
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
?>
