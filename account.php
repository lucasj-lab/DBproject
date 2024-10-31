<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "database-1-instance-1.cpgoq8m2kfkd.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "Bagflea3!";
$dbname = "CraigslistDB";

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch listings for the logged-in user
$stmt = $conn->prepare("SELECT Listing_ID, Title, Description, Price, Date_Posted FROM listings WHERE User_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>My Account</h1>
        <nav>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="post_ad.html">New Listing</a></li>
                <li><a href="listings.html">View All Listings</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="listings">
        <h2>Your Listings</h2>

        <?php if (!empty($listings)) : ?>
            <ul>
                <?php foreach ($listings as $listing) : ?>
                    <li>
                        <h3><?php echo htmlspecialchars($listing['Title']); ?></h3>
                        <p><?php echo htmlspecialchars($listing['Description']); ?></p>
                        <p>Price: $<?php echo htmlspecialchars($listing['Price']); ?></p>
                        <p>Date Posted: <?php echo htmlspecialchars($listing['Date_Posted']); ?></p>
                        <a href="edit_listing.php?listing_id=<?php echo $listing['Listing_ID']; ?>">Edit</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>You have no listings. <a href="post_ad.html">Create one here</a>.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2024 Craigslist 2.0 | All rights reserved</p>
    </footer>
</body>
</html>
