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
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Sign up</a></li>
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
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Sign up</a></li>
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
            <?php if (!empty($listings[0]['Listing_ID'])): ?>
                <?php foreach ($listings as $listing): ?>
                    <div class="listing-item">
                        <img src="<?= $listing['Image_URL'] ?? 'no_image.png'; ?>" alt="Listing Image" class="listing-image">
                        <h3><?= htmlspecialchars($listing['Title']); ?></h3>
                        <p><strong>Price:</strong> $<?= htmlspecialchars($listing['Price']); ?></p>
                        <p><strong>Posted by:</strong> <?= htmlspecialchars($listing['User_Name']); ?></p>
                        <p><strong>Category:</strong> <?= htmlspecialchars($listing['Category_Name']); ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($listing['City']); ?>,
                            <?= htmlspecialchars($listing['State']); ?></p>
                        <p><strong>Posted on:</strong>
                            <?= htmlspecialchars($listing['Formatted_Date'] ?? "Date not available"); ?></p>
                        <button type="button" class="pill-button"
                            onclick="window.location.href='listing_details.php?id=<?= isset($listing['Listing_ID']) ? htmlspecialchars($listing['Listing_ID']) : 0; ?>'">
                            View Listing
                        </button>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <p><?= htmlspecialchars($listings['message']); ?></p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Rookies 2.0 | All rights reserved.</p>
        <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
    </footer>


    <!-- Add styles and hamburger CSS -->
    <style>
        /* General Reset and Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f5f5f5;
            color: #333;
        }

        header {
            background-color: #1a73e8;
            color: #fff;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .logo h1 {
            margin-left: 3em;
            font-size: 1.8rem;

        }

        .desktop-menu {
            display: flex;
            list-style: none;
        }

        .desktop-menu li {
            margin-left: 1rem;
        }

        .desktop-menu li a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }

        /* Hamburger Menu for Mobile */
        .hamburger {
            display: none;
            font-size: 1.8rem;
            color: #fff;
            cursor: pointer;
        }

        .mobile-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: #1a73e8;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0.2rem 0.2rem 0.5rem rgba(0, 0, 0, 0.1);
        }

        .mobile-menu.active {
            display: block;
        }

        .mobile-menu ul {
            display: flex;
            flex-direction: column;
            list-style: none;
        }

        .mobile-menu ul li {
            margin-bottom: 1rem;
        }

        .mobile-menu ul li a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .desktop-menu {
                display: none;
            }

            .hamburger {
                display: block;
            }
        }

        /* Listings Section */
        .listing-item {
            background-color: #fff;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .listing-item h3 {
            margin: 0.5rem 0;
        }
    </style>
</body>

</html>