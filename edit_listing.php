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

try {
    // Fetch listing details
    $stmt = $pdo->prepare("SELECT Title, Description, Price, State, City, Category_ID FROM listings WHERE Listing_ID = :listing_id AND User_ID = :user_id");
    $stmt->execute([':listing_id' => $listing_id, ':user_id' => $user_id]);
    $listing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($listing) {
        $title = $listing['Title'];
        $description = $listing['Description'];
        $price = $listing['Price'];
        $state = $listing['State'];
        $city = $listing['City'];
        $category_id = $listing['Category_ID'];
    } else {
        $error_message = "Listing not found or you do not have permission to edit this listing.";
    }

    // Fetch category name
    $category_stmt = $pdo->prepare("SELECT Category_Name FROM category WHERE Category_ID = :category_id");
    $category_stmt->execute([':category_id' => $category_id]);
    $category = $category_stmt->fetchColumn();

    // Fetch images associated with the listing
    $image_stmt = $pdo->prepare("SELECT file_path FROM images WHERE listing_id = :listing_id");
    $image_stmt->execute([':listing_id' => $listing_id]);
    while ($image = $image_stmt->fetch(PDO::FETCH_ASSOC)) {
        $images[] = $image['file_path'];
    }

    // Handle form submission for updating the listing
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $state = $_POST['state'] ?? '';
        $city = $_POST['city'] ?? '';
        $category = ucfirst(strtolower(trim($_POST['category'])));
        
        // Get category ID for the selected category
        $category_id = getCategoryID($pdo, $category);
        if ($category_id === false) {
            $error_message = "Invalid category selected.";
        } else {
            // Update listing details
            $updateStmt = $pdo->prepare("UPDATE listings SET Title = :title, Description = :description, Price = :price, State = :state, City = :city, Category_ID = :category_id WHERE Listing_ID = :listing_id AND User_ID = :user_id");
            $updateStmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':price' => $price,
                ':state' => $state,
                ':city' => $city,
                ':category_id' => $category_id,
                ':listing_id' => $listing_id,
                ':user_id' => $user_id
            ]);

            // Handle new images if uploaded
            if (!empty($_FILES['images']['name'][0])) {
                $uploadDir = '/var/www/html/uploads/';
                $imageInsertStmt = $pdo->prepare("INSERT INTO images (listing_id, file_path) VALUES (:listing_id, :file_path)");

                foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                    $fileName = basename($_FILES['images']['name'][$key]);
                    $targetPath = $uploadDir . $fileName;
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $file_path = 'uploads/' . $fileName;
                        $imageInsertStmt->execute([
                            ':listing_id' => $listing_id,
                            ':file_path' => $file_path
                        ]);
                    }
                }
            }

            header("Location: user_dashboard.php");  // Redirect after successful update
            exit();
        }
    }
} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}

/**
 * Helper function to get the Category ID from the category name
 */
function getCategoryID($pdo, $categoryName) {
    $stmt = $pdo->prepare("SELECT Category_ID FROM category WHERE Category_Name = :category_name");
    $stmt->execute([':category_name' => $categoryName]);
    return $stmt->fetchColumn();
}

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
                <?php foreach ($images as $image): ?>
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
