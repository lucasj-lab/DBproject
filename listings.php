<?php
// Database connection setup (assuming $pdo is already connected)
try {
    if (!isset($_GET['listing_id']) || !is_numeric($_GET['listing_id'])) {
        echo "No listing ID provided in URL.";
        exit;
    }

    $listing_id = (int)$_GET['listing_id'];

    // SQL query to fetch the listing data with the correct column names
    $sql = "SELECT Title, Description, Price, User_ID, Category_ID, Image_URL, Date_Posted, City, State 
            FROM listings 
            WHERE Listing_ID = :listing_id";

    // Prepare and execute the query using PDO
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':listing_id', $listing_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the listing details
    $listing = $stmt->fetch(PDO::FETCH_ASSOC);

    // If listing is not found
    if (!$listing) {
        echo "No listing found for ID: $listing_id";
        exit;
    }

} catch (PDOException $e) {
    // Handle any PDO exceptions
    echo "Error: " . $e->getMessage();
    exit;
}
?>

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
    <main>
        <section>
            <h1>Listing Details</h1>
            <form class="listing-details-form">
                <?php if (!empty($listing['Image_URL'])): ?>
                    <img src="<?php echo htmlspecialchars($listing['Image_URL']); ?>" alt="Listing Image" class="listing-image">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($listing['Title']); ?></h3>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($listing['Description']); ?></p>
                <p><strong>Price:</strong> $<?php echo htmlspecialchars($listing['Price']); ?></p>
                <p><strong>Posted by User ID:</strong> <?php echo htmlspecialchars($listing['User_ID']); ?></p>
                <p><strong>Category ID:</strong> <?php echo htmlspecialchars($listing['Category_ID']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($listing['City'] . ', ' . $listing['State']); ?></p>
                <p><strong>Date Posted:</strong>
                    <?php
                    $datePosted = new DateTime($listing['Date_Posted']);
                    echo htmlspecialchars($datePosted->format('l, F jS, Y'));
                    ?>
                </p>
                <a href="listings.php" class="pill-button back-to-listings">Back to Listings</a>
            </form>
        </section>
    </main>
    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>
</html>
