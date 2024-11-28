<?php
require 'database_connection.php';
session_start();

// Retrieve the search query
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

// Initialize the listings array
$listings = [];

if (!empty($searchQuery)) {
    try {
        // Prepare the SQL query to fetch search results
        $sql = "
            SELECT 
                listings.Listing_ID,
                listings.Title,
                listings.Description,
                listings.Price,
                listings.Date_Posted AS Formatted_Date,
                listings.City,
                listings.State,
                category.Category_Name,
                user.Name AS User_Name,
                images.Image_URL AS Thumbnail_Image
            FROM listings
            LEFT JOIN category ON listings.Category_ID = category.Category_ID
            LEFT JOIN user ON listings.User_ID = user.User_ID
            LEFT JOIN images ON listings.Listing_ID = images.Listing_ID AND images.Is_Thumbnail = 1
            WHERE listings.Title LIKE ? OR listings.Description LIKE ?
            ORDER BY listings.Date_Posted DESC
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("SQL query preparation failed: " . $conn->error);
        }

        // Bind the search query with wildcards
        $searchTerm = '%' . $searchQuery . '%';
        $stmt->bind_param('ss', $searchTerm, $searchTerm);
        $stmt->execute();

        // Fetch the results
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $listings[] = $row;
        }

        $stmt->close();
    } catch (Exception $e) {
        error_log("Error fetching search results: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <title>Search Results</title>
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <h1>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h1>
    <div class="listings-container">
        <?php if (!empty($listings)): ?>
            <?php foreach ($listings as $listing): ?>
                <div class="listing-item">
                    <!-- Thumbnail -->
                    <?php if (!empty($listing['Thumbnail_Image'])): ?>
                        <img src="<?php echo htmlspecialchars($listing['Thumbnail_Image']); ?>" alt="<?php echo htmlspecialchars($listing['Title']); ?>">
                    <?php else: ?>
                        <img src="uploads/default-thumbnail.jpg" alt="No Image Available">
                    <?php endif; ?>

                    <!-- Title -->
                    <h3><?php echo htmlspecialchars($listing['Title']); ?></h3>

                    <!-- Price -->
                    <p><strong>Price:</strong> 
                        <?php 
                        $price = isset($listing['Price']) ? (float)$listing['Price'] : null;
                        echo ($price === 0.0) ? 'Free' : ('$' . number_format($price, 2));
                        ?>
                    </p>

                    <!-- View Listing Button -->
                    <button class="pill-button" onclick="window.location.href='listing_details.php?listing_id=<?php echo htmlspecialchars($listing['Listing_ID']); ?>'">
                        View Listing
                    </button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No results found for "<?php echo htmlspecialchars($searchQuery); ?>".</p>
        <?php endif; ?>
    </div>
</main>
</body>
<?php include 'footer.php'; ?>
</html>
