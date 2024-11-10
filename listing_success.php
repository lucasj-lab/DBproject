<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listing Created Successfully</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="success-message-container">
        <div class="success-message">
            <h2>Listing Created Successfully!</h2>
            <p>Your listing has been created and is now active.</p>
            <div class="success-options">
                <button onclick="window.location.href='listings.php'">View All Listings</button>
                <button onclick="window.location.href='user_dashboard.php'">View My Listings</button>
                <button onclick="window.location.href='create_listing.php'">Create Another Listing</button>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
