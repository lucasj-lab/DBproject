<?php
require 'database_connection.php';

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


<script>
    // Get the raw date from the data attribute
    const dateElement = document.getElementById('Date-Posted');
    const rawDate = dateElement.getAttribute('data-date');

    // Convert to a Date object
    const date = new Date(rawDate);

    // Format the date (e.g., Friday, November 1st, 2024, 2:30 PM)
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true };
    const formattedDate = date.toLocaleString('en-US', options);

    // Update the element’s text
    dateElement.textContent += formattedDate;
</script>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($listing['Title']); ?></title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>

<body>

    <?php include 'header.php'; ?>


    <sect>

            <h1> Listing Details </h1>

        <!-- Form wrapper for centered listing details -->
        <form class="listing-details-form">
            <?php if (!empty($listing['Image_URL'])): ?>
                <img src="<?php echo htmlspecialchars($listing['Image_URL']); ?>" alt="Listing Image" class="listing-image">
            <?php endif; ?>
            <h3><?php echo htmlspecialchars($listing['Title']); ?></h3>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($listing['Description']); ?></p>
            <p><strong>Price:</strong> $<?php echo htmlspecialchars($listing['Price']); ?></p>
            <p><strong>Posted by:</strong> <?php echo htmlspecialchars($listing['User_Name']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($listing['Category_Name']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($listing['City'] . ', ' . $listing['State']); ?>
            </p>
            <p><strong>Date Posted:</strong>
                <?php
                $datePosted = new DateTime($listing['Date_Posted']);
                echo htmlspecialchars($datePosted->format('l, F jS, Y'));
                ?>
            </p>
            <!-- Back to Listings button -->
            <a href="listings.php" class="pill-button back-to-listings">Back to Listings</a>
        </form>
        </main>


        <footer>
            <?php include 'footer.php'; ?>
        </footer>

</body>

</html>