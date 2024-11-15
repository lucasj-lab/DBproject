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
$title = $description = "";
$price = 0.0;

// Fetch listing details
$stmt = $conn->prepare("SELECT Title, Description, Price FROM listings WHERE Listing_ID = ? AND User_ID = ?");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$stmt->bind_result($title, $description, $price);

if (!$stmt->fetch()) {
    $error_message = "Listing not found or you do not have permission to edit this listing.";
}

$stmt->close();

// Handle form submission for updating the listing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;

    $updateStmt = $conn->prepare("UPDATE listings SET Title = ?, Description = ?, Price = ? WHERE Listing_ID = ? AND User_ID = ?");
    $updateStmt->bind_param("ssdii", $title, $description, $price, $listing_id, $user_id);

    if ($updateStmt->execute()) {
        header("Location: account.php");  // Redirect after successful update
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

<body>
    <?php include 'header.php'; ?>

<!-- JavaScript function for mobile menu toggle -->


    <div class="edit-listing">
        <?php if (!empty($error_message)) : ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="title">Title:</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" required>

            <label for="description">Description:</label>
            <textarea name="description" required><?php echo htmlspecialchars($description); ?></textarea>

            <label for="price">Price:</label>
            <input type="number" name="price" value="<?php echo htmlspecialchars($price); ?>" required>

            <button type="submit">Update Listing</button>
        </form>
    </div>

</body>
<footer>
    <?php include 'footer.php'; ?>
</footer>

</html>