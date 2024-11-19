<?php

require 'database_connection.php';

// Verify connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Function to fetch all listings
function getAllListings($conn) {
    $sql = "
        SELECT 
            listings.Listing_ID,
            listings.Title,
            listings.Description,
            listings.Price,
            listings.Date_Posted,
            listings.State,
            listings.City,
            category.Category_Name,
            `user`.Name AS User_Name,
            images.Image_URL AS Thumbnail_Image
        FROM listings
        LEFT JOIN category ON listings.Category_ID = category.Category_ID
        LEFT JOIN `user` ON listings.User_ID = `user`.User_ID
        LEFT JOIN images ON listings.Listing_ID = images.Listing_ID AND images.Is_Thumbnail = 1
        ORDER BY listings.Date_Posted DESC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL preparation failed: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $listings = [];
    while ($row = $result->fetch_assoc()) {
        $row['Thumbnail_Image'] = $row['Thumbnail_Image'] 
            ? "http://3.146.237.94/uploads/" . $row['Thumbnail_Image']
            : null;
        $listings[] = $row;
    }

    return $listings;
}

// Fetch listings for the HTML page
$listings = [];
try {
    $listings = getAllListings($conn);
} catch (Exception $e) {
    error_log("Error fetching listings: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listings</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your stylesheet -->
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <div class="listings">
        <h1>All Listings</h1>
        <?php if (!empty($listings)): ?>
            <div class="listings-container">
                <?php foreach ($listings as $listing): ?>
                    <div class="listing-container">
                        <div class="listing-title"><?php echo htmlspecialchars($listing['Title']); ?></div>
                        <div class="listing-price">Price: $<?php echo htmlspecialchars($listing['Price']); ?></div>
                        <div class="listing-user">Posted by: <?php echo htmlspecialchars($listing['User_Name']); ?></div>
                        <div class="listing-location">Location: <?php echo htmlspecialchars(($listing['City'] ?? '') . ', ' . ($listing['State'] ?? '')); ?></div>
                        <div class="listing-date">Posted on: <?php echo htmlspecialchars($listing['Date_Posted']); ?></div>

                        <!-- Image Thumbnail -->
                        <?php if (!empty($listing['Thumbnail_Image'])): ?>
                            <div class="image-gallery">
                                <img src="<?php echo htmlspecialchars($listing['Thumbnail_Image']); ?>" alt="Listing Image">
                            </div>
                        <?php else: ?>
                            <p>No image available for this listing.</p>
                        <?php endif; ?>

                        <a href="listing_details.php?listing_id=<?php echo $listing['Listing_ID']; ?>" class="view-button">View Listing</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No listings available at this time.</p>
        <?php endif; ?>
    </div>
</main>
<?php include 'footer.php'; ?>
</body>
</html>
