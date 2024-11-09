<?php
// Start the session to check if the user is logged in, if needed
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'database_connection.php';

// Fetch search term from the request
$searchTerm = $_GET['q'] ?? ''; // Using GET for search term
$searchTerm = '%' . $pdo->quote($searchTerm) . '%';

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
        listings.Title LIKE :searchTerm OR listings.Description LIKE :searchTerm
    ORDER BY 
        listings.Date_Posted DESC
";

try {
    // Prepare and execute the query using PDO
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();

    // Fetch the listings
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the date for each listing
    foreach ($listings as &$listing) {
        if (!empty($listing['Date_Posted'])) {
            $datePosted = new DateTime($listing['Date_Posted']);
            $listing['Formatted_Date'] = $datePosted->format('l, F jS, Y'); // Format the date
        } else {
            $listing['Formatted_Date'] = "Date not available";
        }
    }

    // If no listings are found
    if (empty($listings)) {
        $listings = ["message" => "No listings found for your search."];
    }

} catch (PDOException $e) {
    // Handle any PDO exceptions
    echo "Error: " . $e->getMessage();
    exit;
}

// Close the database connection
$pdo = null;
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
        <?php include 'header.php'; ?>
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
                        <img src="<?= htmlspecialchars($listing['Image_URL'] ?? 'no_image.png'); ?>" alt="Listing Image" class="listing-image">
                        <h3><?= htmlspecialchars($listing['Title']); ?></h3>
                        <p><strong>Price:</strong> $<?= htmlspecialchars($listing['Price']); ?></p>
                        <p><strong>Posted by:</strong> <?= htmlspecialchars($listing['User_Name']); ?></p>
                        <p><strong>Category:</strong> <?= htmlspecialchars($listing['Category_Name']); ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($listing['City']); ?>, <?= htmlspecialchars($listing['State']); ?></p>
                        <p><strong>Posted on:</strong> <?= htmlspecialchars($listing['Formatted_Date']); ?></p>
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
        <?php include 'footer.php'; ?>
    </footer>

</body>

</html>
