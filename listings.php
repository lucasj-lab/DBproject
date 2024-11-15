<? 
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'database_connection.php';

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

// Check if POST request is made for creating a new listing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    $category = $_POST['category'] ?? null;
    $title = $_POST['title'] ?? null;
    $description = $_POST['description'] ?? null;
    $price = $_POST['price'] ?? null;
    $state = $_POST['state'] ?? null;
    $city = $_POST['city'] ?? null;

    // Validate required fields
    if (!$user_id || !$category || !$title || !$description || !$price || !$state || !$city) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit();
    }

    // Get Category_ID
    $category_id = getCategoryID($conn, $category);
    if ($category_id === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid category selected.']);
        exit();
    }

    // Prepare and execute the INSERT query
    $stmt = $conn->prepare("INSERT INTO listings (Title, Description, Price, Date_Posted, User_ID, Category_ID, State, City) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssissss", $title, $description, $price, $user_id, $category_id, $state, $city);
        if ($stmt->execute()) {
            $listing_id = $stmt->insert_id;

            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/heic', 'image/heif'];
                $uploadDirectory = 'uploads/';
                if (!is_dir($uploadDirectory)) {
                    mkdir($uploadDirectory, 0777, true);
                }

                foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                    $fileType = mime_content_type($tmpName);
                    if (in_array($fileType, $allowedTypes)) {
                        $imageName = basename($_FILES['images']['name'][$key]);
                        $uniqueImageName = time() . "_" . $imageName;
                        $targetFilePath = $uploadDirectory . $uniqueImageName;

                        if (move_uploaded_file($tmpName, $targetFilePath)) {
                            $imageUrl = $targetFilePath;
                            $imageSql = "INSERT INTO images (image_url, listing_id) VALUES (?, ?)";
                            $imgStmt = $conn->prepare($imageSql);
                            $imgStmt->bind_param("si", $imageUrl, $listing_id);
                            $imgStmt->execute();
                            $imgStmt->close();
                        }
                    }
                }
            }
            echo json_encode(['success' => true, 'message' => 'Listing created successfully!', 'listing_id' => $listing_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create listing.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: Failed to prepare statement.']);
    }
}

// Fetch existing listings for display
$listings = [];
$sql = "SELECT Listing_ID, Title, Description, Price, Date_Posted, User_ID, Category_ID, State, City, Image_URL 
        FROM listings";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($listing = $result->fetch_assoc()) {
        $listings[] = $listing;
    }
} else {
    echo "No listings found.";
}
$conn->close();
?>

</body>
</html>
