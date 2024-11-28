<?php
require 'database_connection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the search query from the request
$searchQuery = $_GET['q'] ?? '';

try {
    // Prepare the query to fetch listings
    $sql = "
        SELECT 
            l.Listing_ID, 
            l.Title, 
            l.Description, 
            l.Price, 
            l.Date_Posted, 
            l.State, 
            l.City, 
            c.Category_Name, 
            u.Name AS User_Name, 
            i.Image_URL AS Thumbnail_Image
        FROM 
            listings l
        JOIN 
            user u ON l.User_ID = u.User_ID
        JOIN 
            category c ON l.Category_ID = c.Category_ID
        LEFT JOIN 
            images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
        WHERE 
            l.Title LIKE CONCAT('%', ?, '%') OR 
            l.Description LIKE CONCAT('%', ?, '%') OR 
            c.Category_Name LIKE CONCAT('%', ?, '%') OR 
            l.City LIKE CONCAT('%', ?, '%') OR 
            l.State LIKE CONCAT('%', ?, '%')
        ORDER BY 
            l.Date_Posted DESC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL preparation failed: " . $conn->error);
    }

    // Bind parameters and execute the query
    $stmt->bind_param("sssss", $searchQuery, $searchQuery, $searchQuery, $searchQuery, $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Query execution failed: " . $conn->error);
    }

    // Fetch the listings
    $listings = [];
    while ($row = $result->fetch_assoc()) {
        $datePosted = $row['Date_Posted'] ? new DateTime($row['Date_Posted']) : null;
        $row['Formatted_Date'] = $datePosted ? $datePosted->format('F j, Y') : "Date not available";
        $listings[] = $row;
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log("Error fetching listings: " . $e->getMessage());
    $listings = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Listings</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>"> <!-- Cache-busting technique -->
</head>
<body>
    <main>
        <div class="search-results">
            <?php if (!empty($searchResults)): ?>
                <div class="results-container">
                    <?php foreach ($searchResults as $listing): ?>
                        <div class="result-item">
                            <!-- Thumbnail -->
                            <?php 
                            $imagePath = $listing['Images'][0] ?? 'images/placeholder.jpg';
                            $imageSrc = file_exists($imagePath) ? $imagePath : 'images/placeholder.jpg';
                            ?>
                            <picture>
                                <source srcset="<?php echo htmlspecialchars($imageSrc); ?>" type="image/webp">
                                <img src="<?php echo htmlspecialchars($imageSrc); ?>" alt="Thumbnail" class="result-image">
                            </picture>

                            <!-- Title -->
                            <h3><?php echo htmlspecialchars($listing['Title']); ?></h3>

                            <!-- Price -->
                            <p class="listing-price">
                            <?php 
                                if (isset($listing['Price'])) {
                                    $price = (float)$listing['Price'];
                                    echo $price === 0.0 
                                        ? 'Free' 
                                        : '$' . number_format($price, 2);
                                } else {
                                    echo 'N/A';
                                }
                            ?>
                            </p>

                            <!-- View Listing Button -->
                            <button type="button" class="pill-button"
                                onclick="window.location.href='listing_details.php?listing_id=<?php echo htmlspecialchars($listing['Listing_ID']); ?>'">
                                View Listing
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No search results found.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
<?php include 'footer.php'; ?>
</html>