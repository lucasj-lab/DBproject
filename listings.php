<?php
require 'database_connection.php';

$listings = []; // Initialize an empty array for listings

// Query to retrieve active listings from the database
$sql = "SELECT Title, Description, Price, User_ID, Category_ID, City, State, Date_Posted, Image_URL FROM listings";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $listings = $result->fetch_all(MYSQLI_ASSOC); // Fetch all results as an associative array
} else {
    echo "No listings found.";
}

$conn->close();
?>

<body>
    <?php include 'header.php'; ?>

    <div class="listings-container">
        <h2>Active Listings</h2>

        <div id="listings">
            <?php if (!empty($listings)): ?>
                <?php foreach ($listings as $listing): ?>
                    <div class="listing-container">
                        <img src="<?= htmlspecialchars($listing['Image_URL'] ?? 'no_image.png'); ?>" alt="Listing Image"
                            class="listing-image">
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
                        <?php if (!empty($listing['Image_URL'])): ?>
                            <img src="<?php echo htmlspecialchars($listing['Image_URL']); ?>" alt="Listing Image" class="listing-image">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No listings available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
