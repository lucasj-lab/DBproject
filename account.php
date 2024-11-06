<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'database_connection.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT Listing_ID, Title, Description, Price, Date_Posted FROM listings WHERE User_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="listings">
        <h2>Your Listings</h2>
        <?php if (!empty($listings)): ?>
            <ul>
                <?php foreach ($listings as $listing): ?>
                    <li class="listing-item">
                        <h3><?php echo htmlspecialchars($listing['Title']); ?></h3>
                        <p><?php echo htmlspecialchars($listing['Description']); ?></p>
                        <p>$<?php echo htmlspecialchars($listing['Price']); ?></p>
                        <p><strong>Date Posted:</strong>
                            <?php
                            $datePosted = new DateTime($listing['Date_Posted']);
                            echo htmlspecialchars($datePosted->format('l, F jS, Y'));
                            ?>
                        </p>
                        <!-- Back to Listings button -->
                        <a href="create_listing.php" class="pill-button">New Listing</a>
                        <a href="edit_listing.php?id=<?php echo $listing_id; ?>" class="pill-button">Edit Listing</a>
                    <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>You have no listings. <a href="create_listing.php">Create one here</a>.</p>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>