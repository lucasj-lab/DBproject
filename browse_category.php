<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'database_connection.php';

// Check if category is set in the GET request
$category = $_GET['category'] ?? '';

// Prepare and execute the query
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
        c.Category_Name = ?
    GROUP BY 
        l.Listing_ID
    ORDER BY 
        l.Date_Posted DESC
");
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

$listings = [];
while ($row = $result->fetch_assoc()) {
    // Convert image URLs into an array
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
    <title>Browse <?php echo htmlspecialchars($category); ?> Listings</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <div class="listings">
        <?php if (!empty($listings)): ?>
            <div class="listings-container">
                <?php foreach ($listings as $listing): ?>
                    <form class="listing-item" action="listing_details.php" method="GET">
                        <input type="hidden" name="listing_id" value="<?php echo htmlspecialchars($listing['Listing_ID']); ?>">
                        
                        <!-- Thumbnail Image -->
                        <img src="<?php echo htmlspecialchars($listing['Images'][0] ?? 'no_image.png'); ?>" alt="Thumbnail" class="listing-image">

                        <!-- Title -->
                        <h3><?php echo htmlspecialchars($listing['Title']); ?></h3>

                        <!-- Description -->
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($listing['Description']); ?></p>

                        <!-- Price -->
                        <p><strong>Price:</strong> 
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


                        <!-- Category -->
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($listing['Category_Name']); ?></p>

                        <!-- Location -->
                        <p><strong>Location:</strong> <?php echo htmlspecialchars(($listing['City'] ?? '') . ', ' . ($listing['State'] ?? '')); ?></p>

                           <!-- Posted By -->
                           <p><strong>Posted:</strong> <?php echo htmlspecialchars($listing['User_Name']); ?></p>
                           
                        <!-- Date Posted -->
                        <?php 
                        // Format the Date_Posted
                        $formattedDate = !empty($listing['Date_Posted']) 
                            ? (new DateTime($listing['Date_Posted']))->format('F j, Y') 
                            : "Date not available"; 
                        ?>
                        <p><strong>Added:</strong> <?php echo htmlspecialchars($formattedDate); ?></p>

                        <!-- View Listing Button -->
                        <button type="submit" class="pill-button">View Listing</button>
                    </form>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No listings found in the <?php echo htmlspecialchars($category); ?> category.</p>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
</body>
</html>
