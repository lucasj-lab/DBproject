<?php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sanitize session variables to prevent XSS
$isAdmin = $_SESSION['is_admin'] ?? false;
$username = htmlspecialchars($_SESSION['name'] ?? 'User');

// Include database connection
require 'database_connection.php';

// Check if category ID is provided in the URL
$categoryId = $_GET['category_id'] ?? null;
$listings = [];

if ($categoryId && is_numeric($categoryId)) { // Ensure the category ID is numeric
    $categoryId = intval($categoryId); // Convert to integer for safety

    // Fetch listings for the selected category using PDO
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
            listings.Category_ID = :categoryId
        ORDER BY 
            listings.Date_Posted DESC
    ";

    // Prepare the SQL statement
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':categoryId', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        foreach ($results as $row) {
            // Format the Date_Posted field
            if (!empty($row['Date_Posted'])) {
                $datePosted = new DateTime($row['Date_Posted']);
                $formattedDate = $datePosted->format('l, F jS, Y');
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
} else {
    $listings = ["message" => "Category ID is missing or invalid."];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Category</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <main>
        <section id="listings">
            <?php if (!empty($listings[0]['Listing_ID'])): ?>
                <?php foreach ($listings as $listing): ?>
                    <div class="listing-item">
                        <img src="<?= htmlspecialchars($listing['Image_URL'] ?? 'no_image.png'); ?>" alt="Listing Image" class="listing-image">
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

    <?php include 'footer.php'; ?>
</body>
</html>
