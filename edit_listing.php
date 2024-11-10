<?php


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
    $image_stmt = $pdo->prepare("SELECT image_url FROM images WHERE listing_id = :listing_id");
    $image_stmt->execute([':listing_id' => $listing_id]);
    while ($image = $image_stmt->fetch(PDO::FETCH_ASSOC)) {
        $images[] = $image['image_url'];
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
                $imageInsertStmt = $pdo->prepare("INSERT INTO images (listing_id, image_url) VALUES (:listing_id, :image_url)");

                foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                    $fileName = basename($_FILES['images']['name'][$key]);
                    $targetPath = $uploadDir . $imageUrl;
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $image_url = 'uploads/' . $imageUrl;
                        $imageInsertStmt->execute([
                            ':listing_id' => $listing_id,
                            ':image_url' => $image_url
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
    <h1>Edit Listing</h1>

<form action="update_listing.php" method="POST" enctype="multipart/form-data">
    <!-- Hidden input to keep the listing ID -->
    <input type="hidden" name="listing_id" value="<?php echo htmlspecialchars($listing_id); ?>">

    <label for="title">Title:</label>
    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($listing['title']); ?>" required>

    <label for="description">Description:</label>
    <textarea id="description" name="description" required><?php echo htmlspecialchars($listing['description']); ?></textarea>

    <label for="price">Price:</label>
    <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($listing['price']); ?>" required>

    <label for="city">City:</label>
    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($listing['city']); ?>" required>

    <label for="state">State:</label>
    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($listing['state']); ?>" required>

    <!-- Display existing images -->
    <div class="image-section">
        <h3>Current Images</h3>
        <?php if (!empty($listing['image_url'])): ?>
            <img src="<?php echo htmlspecialchars($listing['image_url']); ?>" alt="Current Image" class="current-image" style="width: 150px; height: auto;">
        <?php else: ?>
            <p>No images available for this listing.</p>
        <?php endif; ?>
    </div>

    <!-- New image upload with preview -->
    <label for="new_image">Upload New Image:</label>
    <input type="file" id="new_image" name="new_image" accept="image/*" onchange="previewImage(event)">
    <img id="imagePreview" src="#" alt="Image Preview" style="display: none; width: 150px; height: auto; margin-top: 10px;">

    <!-- Save button -->
    <button type="submit" class="pill-button-edit">Update Listing</button>
</form>
    </div>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>

</html>
