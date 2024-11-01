<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$servername = "database-1-instance-1.cpgoq8m2kfkd.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "Bagflea3!";
$dbname = "CraigslistDB";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the category from the URL parameter
$category = $_GET['category'] ?? null;
if (!$category) {
    die("No category specified.");
}

// Prepare and execute a query to fetch listings by category name
$stmt = $conn->prepare("
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
        category.Category_Name = ?
    ORDER BY 
        listings.Date_Posted DESC
");
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

// Prepare listings for display
$listings = [];
while ($row = $result->fetch_assoc()) {
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
    <header>
        <h1>Listings in <?php echo htmlspecialchars($category); ?> Category</h1>
        <nav>
            <a href="index.html">Back to Home</a>
        </nav>
    </header>

    <main>
        <?php if (!empty($listings)): ?>
            <div class="listings-container">
                <?php foreach ($listings as $listing): ?>
                    <form class="listing-item" action="listing_details.php" method="GET">
                        <input type="hidden" name="id" value="<?php echo $listing['Listing_ID']; ?>">
                        <img src="<?php echo htmlspecialchars($listing['Image_URL'] ?? 'no_image.png'); ?>" alt="Listing Image">
                        <h3><?php echo htmlspecialchars($listing['Title']); ?></h3>
                        <p>Price: $<?php echo htmlspecialchars($listing['Price']); ?></p>
                        <p>Posted by: <?php echo htmlspecialchars($listing['User_Name']); ?></p>
                        <p>Location: <?php echo htmlspecialchars($listing['City'] . ', ' . $listing['State']); ?></p>
                        <p>Posted on: <?php echo htmlspecialchars($listing['Date_Posted']); ?></p>
                        <button type="submit" class="pill-button">View Listing</button>
                    </form>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No listings found in the <?php echo htmlspecialchars($category); ?> category.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 Rookielist 2.0 | All rights reserved.</p>
        <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
    </footer>
</body>
</html>

