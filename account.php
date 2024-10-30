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

        <?php include 'account_logic.php'; ?>

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
