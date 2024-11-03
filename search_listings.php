<?php
// Start the session to check if the user is logged in, if needed
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1); 

require 'database_connection.php';

// Fetch search term from the request
$searchTerm = $_GET['q'] ?? ''; // Using GET for search term
$searchTerm = '%' . $conn->real_escape_string($searchTerm) . '%';

// Fetch listings based on search term
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
        listings.Title LIKE ? OR listings.Description LIKE ? 
    ORDER BY 
        listings.Date_Posted DESC
";


// Database query preparation
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// Initialize the listings array
$listings = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Format the Date_Posted field
        if (!empty($row['Date_Posted'])) {
            $datePosted = new DateTime($row['Date_Posted']);
            $formattedDate = $datePosted->format('l, F jS, Y'); // e.g., "Friday, November 1st, 2024"
        } else {
            $formattedDate = "Date not available";
        }
        
        // Add the formatted date to the row array
        $row['Formatted_Date'] = $formattedDate;
        
        // Add the modified row to the listings array
        $listings[] = $row;
    }
} else {
    $listings = ["message" => "No listings found for your search."];
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <h1>Search Results</h1>
    <nav>
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="create_listing.html">New Listing</a></li>
            <li><a href="listings.html">View All Listings</a></li>
            <li><a href="login.html">Login</a></li>
            <li><a href="register.html">Register</a></li>
            <li><a href="about.html">About</a></li>
        </ul>
    </nav>

    <!-- User Icon for User Dashboard -->
    <div class="user-icon">
        <a href="user_dashboard.php">U</a> <!-- "U" for user; replace with initials or preferred character -->
    </div>

    <!-- Hamburger menu icon for mobile view -->
    <div class="hamburger" onclick="toggleMobileMenu()">☰</div>

    <!-- Mobile dropdown menu for smaller screens -->
    <div class="mobile-menu" id="mobileMenu">
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="create_listing.html">New Listing</a></li>
            <li><a href="listings.html">View All Listings</a></li>
            <li><a href="login.html">Login</a></li>
            <li><a href="register.html">Register</a></li>
            <li><a href="about.html">About</a></li>
        </ul>
    </div>
</header>


    <script>
        function toggleMobileMenu() {
            document.getElementById("mobileMenu").classList.toggle("active");
        }
    </script>

    <main>
    <section id="listings">
    <?php if (!empty($listings[0]['Listing_ID'])) : ?>
        <?php foreach ($listings as $listing) : ?>
            <div class="listing-item">
    <img src="<?= $listing['Image_URL'] ?? 'no_image.png'; ?>" alt="Listing Image" class="listing-image">
    <h3><?= htmlspecialchars($listing['Title']); ?></h3>
    <p>Price: $<?= htmlspecialchars($listing['Price']); ?></p>
    <p>Posted by: <?= htmlspecialchars($listing['User_Name']); ?></p>
    <p>Category: <?= htmlspecialchars($listing['Category_Name']); ?></p>
    <p>Location: <?= htmlspecialchars($listing['City']); ?>, <?= htmlspecialchars($listing['State']); ?></p>
    <p>Posted on: <?= htmlspecialchars($listing['Formatted_Date'] ?? "Date not available"); ?></p> <!-- Display formatted date with fallback -->
    <button type="button" class="pill-button" onclick="window.location.href='listing_details.php?id=<?= isset($listing['Listing_ID']) ? htmlspecialchars($listing['Listing_ID']) : 0; ?>'">
        View Listing
    </button>
</div>

        <?php endforeach; ?>
    <?php else : ?>
        <p><?= htmlspecialchars($listings['message']); ?></p>
    <?php endif; ?>
</section>
    </main>

    <footer>
        <p>&copy; 2024 Your Company Name. All rights reserved.</p>
    </footer>
</body>
</html>
