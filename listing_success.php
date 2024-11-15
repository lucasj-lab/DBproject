<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Optionally, retrieve listing details or a message from the previous page
// For example, you might pass the listing ID to this page for display purposes
$listing_id = $_GET['listing_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Listing Created Successfully</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="success-container">
    <h1>Listing Created Successfully!</h1>
    <p>Your listing has been created and is now live.</p>

    <?php if ($listing_id): ?>
        <p>View your listing: 
            <a href="listing_details.php?listing_id=<?php echo htmlspecialchars($listing_id); ?>" class="pill-button">View Listing</a>
        </p>
    <?php endif; ?>

    <div class="navigation-options">
        <a href="create_listing.php" class="pill-button">Create Another Listing</a>
        <a href="listings.php" class="pill-button">Browse All Listings</a>
        <a href="user_dashboard.php" class="pill-button">Go to Dashboard</a>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
