<?php
require 'database_connection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the search query
$searchQuery = trim($_GET['q'] ?? '');

// Validate input
if (empty($searchQuery)) {
    $listings = [];
    echo "No search term provided.";
    exit;
}

// Prepare the SQL query
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
    error_log("SQL preparation failed: " . $conn->error);
    throw new Exception("SQL preparation failed.");
}

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

catch (Exception $e) {
    error_log("Error fetching listings: " . $e->getMessage());
    $listings = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    <?php if ($listing['Thumbnail_Image']): ?>
                        <img src="<?php echo htmlspecialchars($listing['Thumbnail_Image']); ?>" alt="<?php echo htmlspecialchars($listing['Title']); ?>">
                    <?php else: ?>
                        <img src="uploads/default-thumbnail.jpg" alt="No Image Available">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($listing['Title']); ?></h3>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($listing['Description']); ?></p>
                    <p><strong>Price:</strong> $<?php echo htmlspecialchars($listing['Price']); ?></p>
                    <p><strong>Posted by:</strong> <?php echo htmlspecialchars($listing['User_Name']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($listing['Category_Name']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($listing['City']); ?>, <?php echo htmlspecialchars($listing['State']); ?></p>
                    <p><strong>Posted On:</strong> <?php echo htmlspecialchars($listing['Formatted_Date']); ?></p>
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
