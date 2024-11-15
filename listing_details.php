<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);


require 'database_connection.php';
require 'listing_queries.php';

// Check if the Listing_ID is set in the URL
if (isset($_GET['listing_id'])) {
    $listing_id = intval($_GET['listing_id']);

    // Query to fetch listing details using PDO
    $sql = "
    SELECT 
        Listing_ID, Title, Description, Price, Thumbnail_Image, Date_Posted, 
        State, City, Image_URL
    FROM 
        listings
    ORDER BY 
        Date_Posted DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['listing_id' => $listing_id]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);


    try {
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

} else {
    echo "No listing ID provided in URL.";
    exit;
}
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
    <main>
        <div class="listing-details-container">
            <h1><?php echo htmlspecialchars($images[0]['Title']); ?></h1>
            <p><?php echo htmlspecialchars($images[0]['Description']); ?></p>
            <p><strong>Price:</strong> $<?php echo htmlspecialchars($images[0]['Price']); ?></p>

            <div class="image-gallery">
                <!-- Main Thumbnail -->
                <img id="mainImage" src="<?php echo htmlspecialchars($thumbnail); ?>" class="main-image" alt="Main Image">

                <?php if (!empty($additionalImages)): ?>
    <!-- Scrollable Gallery -->
    <div class="thumbnail-scroll-container">
        <?php foreach ($additionalImages as $image): ?>
            <img src="<?php echo htmlspecialchars($image['Image_URL']); ?>" class="thumbnail-scroll-image" 
                 onclick="changeMainImage(this.src)" alt="Additional Image">
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>No images available for this listing.</p>
<?php endif; ?>


            <!-- Listing Details Section -->
            <h3><?php echo htmlspecialchars($listing['Title']); ?></h3>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($listing['Description']); ?></p>
            <p><strong>Price:</strong> $<?php echo htmlspecialchars($listing['Price']); ?></p>
            <p><strong>Posted by:</strong> <?php echo htmlspecialchars($listing['User_Name']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($listing['Category_Name']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($listing['City'] . ', ' . $listing['State']); ?></p>
            <p><strong>Date Posted:</strong>
                <?php
                $datePosted = new DateTime($listing['Date_Posted']);
                echo htmlspecialchars($datePosted->format('l, F jS, Y'));
                ?>
            </p>
            <!-- Back to Listings button -->
            <a href="listings.php" class="pill-button back-to-listings">Back to Listings</a>
        </form>
    </section>
</main>

<script>
    function changeMainImage(src) {
        document.getElementById("mainImage").src = src;
    }
</script>

<footer>
    <?php include 'footer.php'; ?>
</footer>

</body>

</html>