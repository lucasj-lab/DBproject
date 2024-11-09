<?php
// Start session if not already started
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['name'])) {
    header('Location: login.php');
    exit();
}

// Sanitize session variables to prevent XSS
$isAdmin = $_SESSION['is_admin'] ?? false; // Defaults to false if 'is_admin' is not set
$username = htmlspecialchars($_SESSION['name'] ?? 'User'); // Defaults to 'User' if 'username' is not set

require 'database_connection.php';

// Check if category ID is provided in the URL
$categoryId = $_GET['category_id'] ?? null;

if ($categoryId) {
    $categoryId = intval($categoryId); // Sanitize the category ID input
    // Fetch listings for the selected category
    $sql = "
        SELECT 
            listings.Listing_ID, listings.Title, listings.Description, listings.Price, listings.Date_Posted, 
            user.Name AS User_Name, category.Category_Name, listings.State, listings.City, images.Image_URL
        FROM 
            listings
        JOIN 
            user ON listings.User_ID = user.User_ID
        JOIN 
            category ON listings.Category_ID = category.Category_ID
        LEFT JOIN 
            images ON listings.Listing_ID = images.Listing_ID
        WHERE 
            listings.Category_ID = ? 
        ORDER BY 
            listings.Date_Posted DESC
    ";

    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize the listings array
    $listings = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Format the Date_Posted field
            if (!empty($row['Date_Posted'])) {
                $datePosted = new DateTime($row['Date_Posted']);
                $formattedDate = $datePosted->format('l, F jS, Y'); // e.g., "Friday, November 1st, 2024"
            } else {
                $formattedDate = "Date not available";
            }

            // Add the formatted date to the row array
            $row['Formatted_Date'] = $formattedDate;

            // Add the modified row to the listings array
            $listings[] = $row;
        }
    } else {
        $listings = ["message" => "No listings found in this category."];
    }

    // Close the statement and connection
    $stmt->close();
} else {
    $listings = ["message" => "Category ID is missing or invalid."];
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Category</title>
    <link rel="stylesheet" href="styles.css">
    <header>
        <meta http-equiv="X-Content-Type-Options" content="nosniff">
    </header>
</head>

<body>

    <header>
        <?php include 'header.php'; ?>
    </header>

    <main>
        <section id="listings">
            <?php if (!empty($listings[0]['Listing_ID'])): ?>
                <?php foreach ($listings as $listing): ?>
                    <div class="listing-item">
                        <img src="<?= $listing['Image_URL'] ?? 'no_image.png'; ?>" alt="Listing Image" class="listing-image">
                        <h3><?= htmlspecialchars($listing['Title']); ?></h3>
                        <p><strong>Price:</strong> $<?= htmlspecialchars($listing['Price']); ?></p>
                        <p><strong>Posted by:</strong> <?= htmlspecialchars($listing['User_Name']); ?></p>
                        <p><strong>Category:</strong> <?= htmlspecialchars($listing['Category_Name']); ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($listing['City']); ?>, <?= htmlspecialchars($listing['State']); ?></p>
                        <p><strong>Posted on:</strong> <?= htmlspecialchars($listing['Formatted_Date'] ?? "Date not available"); ?></p>
                        <button type="button" class="pill-button"
                            onclick="window.location.href='listing_details.php?id=<?= isset($listing['Listing_ID']) ? htmlspecialchars($listing['Listing_ID']) : 0; ?>'">
                            View Listing
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p><?= htmlspecialchars($listings['message']); ?></p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>

</body>
</html>
