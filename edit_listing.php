<?php
session_start();
require 'database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['listing_id'])) {
    die("No listing ID provided.");
}

$listing_id = intval($_GET['listing_id']);
$user_id = $_SESSION['user_id'];

// Fetch listing details
$stmt = $conn->prepare("
    SELECT Title, Description, Price, State, City 
    FROM listings 
    WHERE Listing_ID = ? AND User_ID = ?
");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$stmt->bind_result($title, $description, $price, $state, $city);

if (!$stmt->fetch()) {
    die("Listing not found or you do not have permission to edit this listing.");
}
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0.0;
    $state = $_POST['state'] ?? '';
    $city = $_POST['city'] ?? '';

    // Update listing details
    $updateStmt = $conn->prepare("
        UPDATE listings 
        SET Title = ?, Description = ?, Price = ?, State = ?, City = ? 
        WHERE Listing_ID = ? AND User_ID = ?
    ");
    $updateStmt->bind_param("ssdssii", $title, $description, $price, $state, $city, $listing_id, $user_id);

    if ($updateStmt->execute()) {
        // Redirect after successful update
        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Listing Updated Successfully</title>
            <link rel='stylesheet' href='styles.css'>
        </head>
        <body>
            <div class='success-message-container'>
                <h2>Your listing has been successfully updated!</h2>
                <a href='user_dashboard.php' class='pill-button'>Go to Dashboard</a>
            </div>
        </body>
        </html>";
        exit();
    } else {
        echo "Error updating listing.";
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
</head>

<body>
<?php include 'header.php'; ?>

<div class="edit-listing-container">
    <h1>Edit Listing</h1>
    <form method="POST">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($title); ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($description); ?></textarea>
        </div>

        <div class="form-group">
            <label for="price">Price:</label>
            <input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($price); ?>" required>
        </div>

        <div class="form-group">
            <label for="state">State:</label>
            <input type="text" id="state" name="state" value="<?= htmlspecialchars($state); ?>" required>
        </div>

        <div class="form-group">
            <label for="city">City:</label>
            <input type="text" id="city" name="city" value="<?= htmlspecialchars($city); ?>" required>
        </div>

        <button type="submit">Update Listing</button>
    </form>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
<?php
session_start();
require 'database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['listing_id'])) {
    die("No listing ID provided.");
}

$listing_id = intval($_GET['listing_id']);
$user_id = $_SESSION['user_id'];

// Fetch listing details
$stmt = $conn->prepare("
    SELECT Title, Description, Price, State, City 
    FROM listings 
    WHERE Listing_ID = ? AND User_ID = ?
");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$stmt->bind_result($title, $description, $price, $state, $city);

if (!$stmt->fetch()) {
    die("Listing not found or you do not have permission to edit this listing.");
}
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0.0;
    $state = $_POST['state'] ?? '';
    $city = $_POST['city'] ?? '';

    // Update listing details
    $updateStmt = $conn->prepare("
        UPDATE listings 
        SET Title = ?, Description = ?, Price = ?, State = ?, City = ? 
        WHERE Listing_ID = ? AND User_ID = ?
    ");
    $updateStmt->bind_param("ssdssii", $title, $description, $price, $state, $city, $listing_id, $user_id);

    if ($updateStmt->execute()) {
        // Redirect after successful update
        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Listing Updated Successfully</title>
            <link rel='stylesheet' href='styles.css'>
        </head>
        <body>
            <div class='success-message-container'>
                <h2>Your listing has been successfully updated!</h2>
                <a href='user_dashboard.php' class='pill-button'>Go to Dashboard</a>
            </div>
        </body>
        </html>";
        exit();
    } else {
        echo "Error updating listing.";
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
    <script>
        function updateCities() {
            const stateSelect = document.getElementById('state');
            const cityDropdown = document.getElementById('city-dropdown');
            const selectedState = stateSelect.value;

            // Replace this with dynamic city data
            const statesAndCities = {
                "AL": ["Birmingham", "Montgomery", "Mobile", "Huntsville", "Tuscaloosa"],
                "AK": ["Anchorage", "Fairbanks", "Juneau", "Sitka", "Ketchikan"],
                "AZ": ["Phoenix", "Tucson", "Mesa", "Chandler", "Glendale"],
                "AR": ["Little Rock", "Fort Smith", "Fayetteville", "Springdale", "Jonesboro"],
                "CA": ["Los Angeles", "San Diego", "San Jose", "San Francisco", "Fresno"],
                "CO": ["Denver", "Colorado Springs", "Aurora", "Fort Collins", "Lakewood"]
            };

            cityDropdown.innerHTML = '<option value="">--Select City--</option>';
            if (selectedState && statesAndCities[selectedState]) {
                statesAndCities[selectedState].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    cityDropdown.appendChild(option);
                });
            }
        }
    </script>
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="create-listing-container">
        <h1 class="edit-listing-title">Edit Listing</h1>
        <form id="create-listing-form" method="POST">
            <div class="listing-form-group">
                <select id="category" name="category" required>
                    <option value="">--Select Category--</option>
                    <option value="Auto">Auto</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Furniture">Furniture</option>
                    <option value="Other">Other</option>
                </select>

                <input type="text" id="title" name="title" placeholder="Title" value="<?= htmlspecialchars($title); ?>" required>
                <textarea id="description" name="description" rows="4" placeholder="Description" required><?= htmlspecialchars($description); ?></textarea>
                <input type="number" step="0.01" id="price" name="price" placeholder="Price" value="<?= htmlspecialchars($price); ?>" required>

                <select id="state" name="state" onchange="updateCities()" required>
                    <option value="AL" <?= $state === "AL" ? "selected" : ""; ?>>Alabama</option>
                    <option value="AK" <?= $state === "AK" ? "selected" : ""; ?>>Alaska</option>
                    <option value="AZ" <?= $state === "AZ" ? "selected" : ""; ?>>Arizona</option>
                    <option value="AR" <?= $state === "AR" ? "selected" : ""; ?>>Arkansas</option>
                    <option value="CA" <?= $state === "CA" ? "selected" : ""; ?>>California</option>
                    <option value="CO" <?= $state === "CO" ? "selected" : ""; ?>>Colorado</option>
                </select>

                <div class="listing-city-group">
                    <select id="city-dropdown" name="city" required>
                        <option value="<?= htmlspecialchars($city); ?>" selected><?= htmlspecialchars($city); ?></option>
                    </select>
                </div>

                <div class="file-upload-container">
                    <label class="form-label" for="images"></label>
                    <input type="file" id="images" name="images[]" class="file-input" accept=".jpg, .jpeg, .png, .heic, .heif" multiple>
                    <label for="images" class="file-upload-button">Choose Files</label>
                    <span class="file-upload-text" id="file-upload-text"></span>
                </div>
                <div id="imagePreviewContainer"></div>
            </div>
            <div class="btn-container">
                <button type="submit">Update</button>
            </div>
        </form>
    </div>
</body>

</html>
