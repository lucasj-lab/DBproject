<?php
session_start();
require 'database_connection.php';

$userId = $_GET['userId'] ?? null; // Default to null if 'userId' is not set
if ($userId) {
    echo '<a href="profile.php?id=' . htmlspecialchars($userId) . '">View Seller\'s Profile</a>';
} else {
    echo 'User information unavailable';
}


$user_id = intval($_GET['user_id']);

// Fetch user details
$stmt = $pdo->prepare("SELECT Name, Profile_Bio, Date_Joined FROM user WHERE User_ID = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Fetch user listings
$listingStmt = $pdo->prepare("SELECT * FROM listings WHERE User_ID = ?");
$listingStmt->execute([$user_id]);
$listings = $listingStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['Name']) ?>'s Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="profile-container">
        <h1><?= htmlspecialchars($user['Name']) ?>'s Profile</h1>
        <p>Member since: <?= htmlspecialchars($user['Date_Joined']) ?></p>
        <p>Bio: <?= htmlspecialchars($user['Profile_Bio'] ?? "No bio available.") ?></p>

        <h2>Listings by <?= htmlspecialchars($user['Name']) ?>:</h2>
        <ul>
            <?php foreach ($listings as $listing): ?>
                <li>
                    <a href="listing_details.php?listing_id=<?= $listing['Listing_ID'] ?>">
                        <?= htmlspecialchars($listing['Title']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <h2>Send a Message</h2>
        <form action="send_message.php" method="POST">
            <input type="hidden" name="recipient_id" value="<?= $user_id ?>">
            <textarea name="message_text" placeholder="Write your message here..." required></textarea>
            <button type="submit">Send</button>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
