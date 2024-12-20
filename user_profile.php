<?php

require 'database_connection.php';

// Get the search query from the user input
$searchQuery = $_GET['q'] ?? '';

// Function to fetch search results
function getSearchResults($conn, $searchQuery) {
    $sql = "
        SELECT 
            listings.Listing_ID,
            listings.Title,
            listings.Description,
            listings.Price,
            listings.Date_Posted,
            listings.State,
            listings.City,
            category.Category_Name,
            `user`.Name AS User_Name,
            thumbnails.Image_URL AS Thumbnail_Image
        FROM listings
        LEFT JOIN category ON listings.Category_ID = category.Category_ID
        LEFT JOIN `user` ON listings.User_ID = `user`.User_ID
        LEFT JOIN (
            SELECT Listing_ID, Image_URL
            FROM images
            WHERE Is_Thumbnail = 1
            GROUP BY Listing_ID
        ) AS thumbnails ON listings.Listing_ID = thumbnails.Listing_ID
        WHERE listings.Title LIKE ? OR listings.Description LIKE ?
        ORDER BY listings.Date_Posted DESC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL preparation failed: " . $conn->error);
    }

    $searchTerm = '%' . $searchQuery . '%';
    $stmt->bind_param('ss', $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $listings = [];
    while ($row = $result->fetch_assoc()) {
        $row['Thumbnail_Image'] = $row['Thumbnail_Image'] 
            ? "http://3.146.237.94/uploads/" . $row['Thumbnail_Image']
            : null;
        $listings[] = $row;
    }

    return $listings;
}

// Fetch search results
$listings = [];
try {
    if (!empty($searchQuery)) {
        $listings = getSearchResults($conn, $searchQuery);
    }
} catch (Exception $e) {
    error_log("Error fetching search results: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your stylesheet -->
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <div class="listings">
        <h1>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h1>
        <?php if (!empty($listings)): ?>
            <div class="listings-container">
                <?php foreach ($listings as $listing): ?>
                    <div class="listing-container">
                        <div class="listing-title"><?php echo htmlspecialchars($listing['Title']); ?></div>
                        <div class="listing-price">Price: $<?php echo htmlspecialchars($listing['Price']); ?></div>
                        <div class="listing-user">Posted by: <?php echo htmlspecialchars($listing['User_Name']); ?></div>
                        <div class="listing-location">Location: <?php echo htmlspecialchars(($listing['City'] ?? '') . ', ' . ($listing['State'] ?? '')); ?></div>
                        <div class="listing-date">Posted on: <?php echo htmlspecialchars($listing['Date_Posted']); ?></div>

                        <!-- Image Thumbnail -->
                        <?php if (!empty($listing['Thumbnail_Image'])): ?>
                            <div class="image-gallery">
                                <img src="<?php echo htmlspecialchars($listing['Thumbnail_Image']); ?>" alt="Listing Image">
                            </div>
                        <?php else: ?>
                            <p>No image available for this listing.</p>
                        <?php endif; ?>

                        <a href="listing_details.php?listing_id=<?php echo $listing['Listing_ID']; ?>" class="view-button">View Listing</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No results found for "<?php echo htmlspecialchars($searchQuery); ?>".</p>
        <?php endif; ?>
    </div>
</main>
<?php include 'footer.php'; ?>

<div id="messagesContainer"></div>
<form id="sendMessageForm">
    <input type="hidden" name="sender_id" value="1">
    <input type="hidden" name="receiver_id" value="2">
    <textarea name="message_text" required></textarea>
    <button type="submit">Send</button>
</form>

<script src="messaging.js"></script>
<script>
    // Fetch messages between these users
    fetchMessages(1, 2);

    // Send a message
    document.getElementById('sendMessageForm').addEventListener('submit', function (e) {
        e.preventDefault();
        sendMessage('sendMessageForm');
    });
</script>
</body>
</html>