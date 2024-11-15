<?php
// Start session if needed (if header.php doesn't start the session)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
require 'database_connection.php';

// Check if the Listing_ID is set in the URL
if (isset($_GET['listing_id'])) {
    $listing_id = intval($_GET['listing_id']);

    // Prepare the query to fetch listing details
    $sql = "
        SELECT 
            listings.Listing_ID, listings.Title, listings.Description, listings.Price, listings.Date_Posted, 
            user.Name AS User_Name, category.Category_Name, listings.State, listings.City, listings.Image_URL
        FROM 
            listings
        JOIN 
            user ON listings.User_ID = user.User_ID
        JOIN 
            category ON listings.Category_ID = category.Category_ID
        WHERE 
            listings.Listing_ID = ?
    ";

    // Use prepared statements to prevent SQL injection
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $listing = $result->fetch_assoc();
        } else {
            echo "No listing found for ID: $listing_id";
            exit;
        }

        $stmt->close();
    } else {
        echo "Query error: " . $conn->error;
        exit;
    }
} else {
    echo "No listing ID provided in URL.";
    exit;
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Include any head content from header.php if necessary -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($listing['Title']); ?></title>
    <link rel="stylesheet" href="styles.css?v=<?= time(); ?>">
</head>

<body>

    <?php include 'header.php'; ?>

    <main>
        <!-- Form wrapper for centered listing details -->
        <div class="listing-details-form">
            <?php if (!empty($listing['Image_URL'])): ?>
                <img src="<?= htmlspecialchars($listing['Image_URL']); ?>" alt="Listing Image" class="listing-image">
            <?php endif; ?>
            <h3><?= htmlspecialchars($listing['Title']); ?></h3>
            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($listing['Description'])); ?></p>
            <p><strong>Price:</strong> $<?= htmlspecialchars($listing['Price']); ?></p>
            <p><strong>Posted by:</strong> <?= htmlspecialchars($listing['User_Name']); ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($listing['Category_Name']); ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($listing['City'] . ', ' . $listing['State']); ?></p>
            <?php
            $datePosted = new DateTime($listing['Date_Posted']);
            echo htmlspecialchars($datePosted->format('l, F jS, Y'));
            ?>
            </p>

            <!-- Back to Listings button -->
            <a href="listings.php" class="pill-button back-to-listings">Back to Listings</a>
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>

</html>