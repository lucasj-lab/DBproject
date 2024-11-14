<?php
require 'database_connection.php';

// Fetch listings with category, user, and image data
if (isset($_GET['id'])) {
    $listing_id = intval($_GET['id']);  // Make sure to cast to integer for security

    // Prepare the SQL query
    $sql = "
        SELECT 
            listings.Listing_ID, listings.Title, listings.Description, listings.Price, listings.Date_Posted, 
            user.Name AS User_Name, category.Category_Name, listings.State, listings.City, images.Image_URL 
        FROM listings
        JOIN user ON listings.User_ID = user.User_ID
        JOIN category ON listings.Category_ID = category.Category_ID
        LEFT JOIN images ON listings.Listing_ID = images.Listing_ID
        WHERE listings.Listing_ID = :listing_id
    ";

    // Prepare the statement
    $stmt = $pdo->prepare($sql);

    // Bind the parameter using PDO syntax
    $stmt->bindParam(':listing_id', $listing_id, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();

    // Fetch the results
    $listingDetails = [];
    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Group images by listing ID
            $listingDetails['details'] = $row;
            $listingDetails['images'][] = $row['Image_URL'];
        }
    }

    // Close the statement (optional with PDO, but good practice)
    $stmt->closeCursor();
} else {
    echo "<p>Listing ID not provided.</p>";
    exit();
}
?>


    <?php include 'header.php'; ?>


<div class="listing-details">
    <?php if (!empty($listingDetails)) : ?>
        <?php $listing = $listingDetails['details']; ?>
        <div class="listing-header">
            <h1><?php echo htmlspecialchars($listing['Title']); ?></h1>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($listing['Category_Name']); ?></p>
            <p><strong>Price:</strong> $<?php echo number_format($listing['Price'], 2); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($listing['City']) . ', ' . htmlspecialchars($listing['State']); ?></p>
            <p><strong>Posted by:</strong> <?php echo htmlspecialchars($listing['User_Name']); ?></p>
            <p><strong>Posted on:</strong> <?php echo date("F j, Y", strtotime($listing['Date_Posted'])); ?></p>
        </div>

        <div class="listing-description">
            <h2>Description</h2>
            <p><?php echo nl2br(htmlspecialchars($listing['Description'])); ?></p>
        </div>

        <div class="listing-images">
            <h3>Images</h3>
            <?php 
                $images = $listingDetails['images'];
                if (empty($images)) {
                    echo "<p>No images available for this listing.</p>";
                } else {
                    foreach ($images as $image) {
                        echo "<img src='/{$image}' alt='Listing Image' style='width:100px;height:auto;'>";
                    }
                }
            ?>
        </div>

        <div class="listing-actions">
            <button onclick="window.location.href='contact_seller.php?user_id=<?php echo $listing['User_ID']; ?>&listing_id=<?php echo $listing['Listing_ID']; ?>'">Contact Seller</button>
            <button onclick="window.location.href='listings.php'">Back to Listings</button>
        </div>
    <?php else : ?>
        <p>Listing details not found.</p>
    <?php endif; ?>
</div>


</body>

<footer>
    <?php include 'footer.php'; ?>
</footer>

</html>
