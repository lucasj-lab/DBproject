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
        $row['Formatted_Date'] = $datePosted ? $datePosted->format('l, F jS, Y') : "Date not available";
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
    <title>Search Results</title>
    <style>
        .listings-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .listing-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .listing-item img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }

        .listing-item h3 {
            margin: 10px 0;
        }

        .pill-button {
            padding: 10px 15px;
            border: none;
            border-radius: 20px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }

        .pill-button:hover {
            background-color: #0056b3;
        }
    </style>
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
