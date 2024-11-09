<?php
// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'database_connection.php';

// Check if listing_id is provided in the URL
if (!isset($_GET['listing_id'])) {
    die("No listing ID provided.");
}

$listing_id = intval($_GET['listing_id']);
$user_id = $_SESSION['user_id'];
$error_message = "";

// Initialize variables
$title = $description = $state = $city = $category = "";
$price = 0.0;
$images = [];

// Fetch listing details
$stmt = $conn->prepare("SELECT Title, Description, Price, State, City, Category_ID FROM listings WHERE Listing_ID = ? AND User_ID = ?");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$stmt->bind_result($title, $description, $price, $state, $city, $category_id);

if (!$stmt->fetch()) {
    $error_message = "Listing not found or you do not have permission to edit this listing.";
}

$stmt->close();

// Fetch category name
$category_stmt = $conn->prepare("SELECT Category_Name FROM category WHERE Category_ID = ?");
$category_stmt->bind_param("i", $category_id);
$category_stmt->execute();
$category_stmt->bind_result($category);
$category_stmt->fetch();
$category_stmt->close();

// Fetch images associated with the listing
$image_stmt = $conn->prepare("SELECT file_path FROM images WHERE listing_id = ?");
$image_stmt->bind_param("i", $listing_id);
$image_stmt->execute();
$image_stmt->bind_result($file_path);
while ($image_stmt->fetch()) {
    $images[] = $file_path;
}
$image_stmt->close();

// Handle form submission for updating the listing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $state = $_POST['state'] ?? '';
    $city = $_POST['city'] ?? '';
    $category = ucfirst(strtolower(trim($_POST['category'])));

    // Update listing details
    $updateStmt = $conn->prepare("UPDATE listings SET Title = ?, Description = ?, Price = ?, State = ?, City = ?, Category_ID = ? WHERE Listing_ID = ? AND User_ID = ?");
    $updateStmt->bind_param("ssdssiii", $title, $description, $price, $state, $city, $category_id, $listing_id, $user_id);

    if ($updateStmt->execute()) {
        // Handle new images if uploaded
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = '/var/www/html/uploads/';
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                $fileName = basename($_FILES['images']['name'][$key]);
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $imageInsertStmt = $conn->prepare("INSERT INTO images (listing_id, file_path) VALUES (?, ?)");
                    $imageInsertStmt->bind_param("is", $listing_id, $targetPath);
                    $imageInsertStmt->execute();
                    $imageInsertStmt->close();
                }
            }
        }

        header("Location: user_dashboard.php");  // Redirect after successful update
        exit();
    } else {
        $error_message = "Error updating listing.";
    }
    $updateStmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .pill-button {
            padding: 10px 20px;
            font-size: 16px;
            color: white;
            background-color: #007bff;
            border-radius: 50px;
            text-decoration: none;
        }

        .image-gallery img {
            width: 100px;
            height: auto;
            margin-right: 10px;
        }

        .image-gallery {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <header>
        <?php include 'header.php'; ?>
    </header>

    <div class="edit-listing-container">
        <h2>Edit Listing</h2>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form action="edit_listing.php?listing_id=<?php echo $listing_id; ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description" id="description" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" name="price" id="price" value="<?php echo htmlspecialchars($price); ?>" required>
            </div>
            <div class="form-group">
                <label for="state">State:</label>
                <input type="text" name="state" id="state" value="<?php echo htmlspecialchars($state); ?>" required>
            </div>
            <div class="form-group">
                <label for="city">City:</label>
                <input type="text" name="city" id="city" value="<?php echo htmlspecialchars($city); ?>" required>
            </div>
            <div class="form-group">
                <label for="category">Category:</label>
                <select name="category" id="category" required>
                    <option value="Auto" <?php echo $category == 'Auto' ? 'selected' : ''; ?>>Auto</option>
                    <option value="Electronics" <?php echo $category == 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                    <option value="Furniture" <?php echo $category == 'Furniture' ? 'selected' : ''; ?>>Furniture</option>
                    <option value="Other" <?php echo $category == 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="images">Upload New Images:</label>
                <input type="file" name="images[]" id="images" multiple>
            </div>

            <div class="image-gallery">
                <label>Current Images:</label>
                <?php foreach ($images as $image) : ?>
                    <img src="/<?php echo htmlspecialchars($image); ?>" alt="Current Listing Image">
                <?php endforeach; ?>
            </div>

            <button type="submit" class="pill-button">Save Changes</button>
        </form>
    </div>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>

</html>
