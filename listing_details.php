<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$servername = "database-1-instance-1.cpgoq8m2kfkd.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "Bagflea3!";
$dbname = "CraigslistDB";

// Create a new connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the Listing_ID is set in the URL
if (isset($_GET['id'])) {
    $listing_id = intval($_GET['id']);

    // Query to fetch listing details
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
            listings.Listing_ID = $listing_id
    ";

    $result = $conn->query($sql);
    if ($result === false) {
        echo "Query error: " . $conn->error;
        exit;
    } elseif ($result->num_rows > 0) {
        $listing = $result->fetch_assoc();
    } else {
        echo "No listing found for ID: $listing_id";
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($listing['Title']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1><?php echo htmlspecialchars($listing['Title']); ?></h1>
        <nav>
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="create_listing.html">New Listing</a></li>
                <li><a href="listings.html">View All Listings</a></li>
                <li><a href="login.html">Login</a></li>
                <li><a href="about.html">About</a></li>
            </ul>
        </nav>
        <div class="hamburger" onclick="toggleMobileMenu()">☰</div>
        <div class="mobile-menu" id="mobileMenu">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="create_listing.html">New Listing</a></li>
                <li><a href="listings.html">View All Listings</a></li>
                <li><a href="login.html">Login</a></li>
                <li><a href="about.html">About</a></li>
            </ul>
        </div>
    </header>

    <main class="listing-details">
        <?php if (!empty($listing['Image_URL'])): ?>
            <img src="<?php echo htmlspecialchars($listing['Image_URL']); ?>" alt="Listing Image" class="listing-image">
        <?php endif; ?>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($listing['Description']); ?></p>
        <p><strong>Price:</strong> $<?php echo htmlspecialchars($listing['Price']); ?></p>
        <p><strong>Posted by:</strong> <?php echo htmlspecialchars($listing['User_Name']); ?></p>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($listing['Category_Name']); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($listing['City'] . ', ' . $listing['State']); ?></p>
        <p><strong>Date Posted:</strong> <?php echo htmlspecialchars($listing['Date_Posted']); ?></p>
        <br>
        <a href="listings.html" class="pill-button">Back to Listings</a>
        </br>
    </main>


    <footer>   
        <p>&copy; 2024 Rookielist 2.0 | All rights reserved.</p>
        <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
    </footer>
    <script>
        function toggleMobileMenu() {
            document.getElementById("mobileMenu").classList.toggle("active");
        }
    </script>
</body>
</html>
