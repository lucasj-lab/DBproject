<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'database_connection.php';

// Get the search query from the request
$searchQuery = $_GET['q'] ?? '';

// Prepare and execute the query to fetch listings that match the search query
$stmt = $conn->prepare("
    SELECT 
        l.Listing_ID, l.Title, l.Description, l.Price, l.Date_Posted,
        u.Name AS User_Name, c.Category_Name, l.State, l.City,
        GROUP_CONCAT(i.Image_URL) AS Images
    FROM 
        listings l
    JOIN 
        user u ON l.User_ID = u.User_ID
    JOIN 
        category c ON l.Category_ID = c.Category_ID
    LEFT JOIN 
        images i ON l.Listing_ID = i.Listing_ID
    WHERE 
        l.Title LIKE CONCAT('%', ?, '%') OR 
        l.Description LIKE CONCAT('%', ?, '%') OR 
        c.Category_Name LIKE CONCAT('%', ?, '%') OR 
        l.City LIKE CONCAT('%', ?, '%') OR 
        l.State LIKE CONCAT('%', ?, '%')
    GROUP BY 
        l.Listing_ID
    ORDER BY 
        l.Date_Posted DESC
");
$stmt->bind_param("sssss", $searchQuery, $searchQuery, $searchQuery, $searchQuery, $searchQuery);
$stmt->execute();
$result = $stmt->get_result();

// Prepare listings for display
$listings = [];
while ($row = $result->fetch_assoc()) {
    $row['Images'] = $row['Images'] ? explode(',', $row['Images']) : [];
    $listings[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <style>
        .listing-container {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .listing-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .listing-price, .listing-user, .listing-location, .listing-date {
            margin: 5px 0;
        }

        .image-gallery {
            display: flex;
            overflow-x: auto;
            gap: 10px;
            margin-top: 10px;
        }

        .image-gallery img {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }

        .image-gallery::-webkit-scrollbar {
            height: 8px;
        }

        .image-gallery::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 4px;
        }

        .image-gallery::-webkit-scrollbar-track {
            background: #f9f9f9;
        }

        .view-button {
            display: block;
            margin: 10px 0;
            padding: 8px 12px;
            background-color: #1a73e8;
            color: #fff;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
        }

        .view-button:hover {
            background-color: #fbbc04;
            color: #000;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <div class="listings">
        <h1>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h1>
        <?php if (!empty($listings)): ?>
            <div class="listings-container">
                <?php foreach ($listings as $listing): ?>
                    <div class="listing-container">
                        <div class="listing-title"><?php echo htmlspecialchars($listing['Title']); ?></div>
                        <div class="listing-price">Price: $<?php echo htmlspecialchars($listing['Price']); ?></div>
                        <div class="listing-user">Posted by: <?php echo htmlspecialchars($listing['User_Name']); ?></div>
                        <div class="listing-location">Location: <?php echo htmlspecialchars(($listing['City'] ?? '') . ', ' . ($listing['State'] ?? '')); ?></div>
                        <div class="listing-date">Posted on: <?php echo htmlspecialchars($listing['Date_Posted']); ?></div>

                        <!-- Image Gallery -->
                        <?php if (!empty($listing['Images'])): ?>
                            <div class="image-gallery">
                                <?php foreach ($listing['Images'] as $image): ?>
                                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Listing Image">
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No images available for this listing.</p>
                        <?php endif; ?>

                        <a href="listing_details.php?listing_id=<?php echo $listing['Listing_ID']; ?>" class="view-button">View Listing</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No results found for "<?php echo htmlspecialchars($searchQuery); ?>".</p>
        <?php endif; ?>
    </div>
</main>
<?php include 'footer.php'; ?>
</body>
</html>
