<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
require 'database_connection.php';

// Check if user_id is available in session
if (!isset($_SESSION['user_id'])) {
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Not Logged In</title>
        <link rel='stylesheet' href='styles.css'>
    </head>
    <body>
        <div class='redirect-message-container'>
            <div class='redirect-message'>
                <h2>You must be logged in to create a listing.</h2>
                <p>Please <a href='login.php'>log in</a> or <a href='signup.php'>sign up</a> to continue.</p>
            </div>
        </div>
    </body>
    </html>";
    exit();
}

// Function to get category ID
function getCategoryID($pdo, $category)
{
    $stmt = $pdo->prepare("SELECT Category_ID FROM category WHERE Category_Name = :category");
    $stmt->bindValue(':category', $category, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchColumn() ?: false;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch form data and sanitize
    $user_id = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $state = trim($_POST['state'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $category = ucfirst(strtolower(trim($_POST['category'] ?? '')));

    // Validate required fields
    if (empty($title) || empty($description) || empty($price) || empty($state) || empty($city) || empty($category)) {
        echo "<p>All fields are required.</p>";
        exit();
    }

    // Get the Category_ID from the database
    $category_id = getCategoryID($pdo, $category);
    if ($category_id === false) {
        echo "<p>Invalid category selected.</p>";
        exit();
    }

    // Insert listing data into the database
    try {
        $stmt = $pdo->prepare("INSERT INTO listings (User_ID, Title, Description, Price, Date_Posted, Category_ID, State, City) 
                               VALUES (:user_id, :title, :description, :price, NOW(), :category_id, :state, :city)");
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, PDO::PARAM_STR);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':state', $state, PDO::PARAM_STR);
        $stmt->bindValue(':city', $city, PDO::PARAM_STR);

        $stmt->execute();

        // Redirect to listing success page
        header("Location: listing_success.php");
        exit();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Listing</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function showSuccessModal() {
            document.getElementById("successModal").style.display = "block";
        }

        function closeSuccessModal() {
            document.getElementById("successModal").style.display = "none";
        }
    </script>
</head>

<body>



    <?php include 'header.php'; ?>

    <div class="creating-listing-form">
        <h2>Create a New Listing</h2>
        <form id="listing-form" action="create_listing.php" method="POST" enctype="multipart/form-data">
            <div class="listing-form-group">
                <input type="text" id="title" name="title" placeholder="Title" required>
                <select id="category" name="category" required>
                    <option value="">--Select Category--</option>
                    <option value="Auto">Auto</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Furniture">Furniture</option>
                    <option value="Other">Other</option>
                </select>
                <textarea id="description" name="description" rows="4" placeholder="Description" required></textarea>
                <input type="number" step="0.01" id="price" name="price" placeholder="Price" required>
                <label for="state" class="form-label">State:</label>
                <select id="state" name="state" class="input-field" onchange="loadCities(this.value)">
                    <option value="">Select a State</option>
                    <!-- Add state options here, e.g., -->
                    <option value="California">California</option>
                    <option value="Texas">Texas</option>
                    <!-- Add more states as needed -->
                </select>

                <label for="city" class="form-label">City:</label>
                <select id="city" name="city" class="input-field">
                    <option value="">Select a City</option>
                    <!-- Cities will be populated dynamically based on selected state -->
                </select>

                <input type="file" name="images[]" multiple>
                <button type="submit">Submit Listing</button>
            </div>
        </form>
    </div>

    <script>
        function showSuccessModal() {
            document.getElementById("successModal").style.display = "block";
        }

        // Simulate showing the modal after successful listing creation
        // In real use, this function call should be triggered only if the server returns success
        showSuccessModal();
    </script>


    <?php include 'footer.php'; ?>


</body>

</html>