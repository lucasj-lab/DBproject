<?php
session_start();
require 'database_connection.php';
require 'listing_queries.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT Name, Email, Date_Joined FROM user WHERE User_ID = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's listings
$sql = "SELECT listings.Listing_ID, listings.Title, listings.Description, listings.Price, listings.Date_Posted, 
               listings.City, listings.State, listings.Thumbnail_Image
        FROM listings 
        WHERE listings.User_ID = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <main class="dope-dashboard">
        <h1 class="dashboard-title">Welcome, <?php echo htmlspecialchars($user['Name']); ?></h1>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
        <p><strong>Member Since:</strong>
            <?php echo htmlspecialchars((new DateTime($user['Date_Joined']))->format('l, F jS, Y')); ?>
        </p>

        <h2>Your Listings</h2>

        <?php if (!empty($listings)): ?>
            <div class="table-container">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Date Posted</th>
                            <th>City</th>
                            <th>State</th>
                            <th>Thumbnail</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listings as $listing): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($listing['Title']); ?></td>
                                <td><?php echo htmlspecialchars($listing['Description']); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($listing['Price'], 2)); ?></td>
                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        !empty($listing['Date_Posted'])
                                        ? (new DateTime($listing['Date_Posted']))->format('l, F jS, Y')
                                        : 'Date not available'
                                    );
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($listing['City']); ?></td>
                                <td><?php echo htmlspecialchars($listing['State']); ?></td>
                                <style>
                                    /* CSS to set the user icon border color when logged in */
                                    .user-icon.logged-in {
                                        background-color: #008000;
                                        /* Modify this style as per your requirements */
                                    }
                                </style>
                                <td>
                                    <div class="dashboard-listing">
                                        <div class="thumbnail-container">
                                        <td> <img src="<?= htmlspecialchars($listing['Thumbnail_Image'] ?? 'placeholder.jpg'); ?>"</td>
                                                alt="Listing Thumbnail" class="thumbnail-image">
                                            <div class="button-container">
                                                <td><a href="edit_listing.php?listing_id=<?= $listing['Listing_ID']; ?>"
                                                    class="pill-button">Edit</a>
                                                <a href="delete_listing.php?listing_id=<?= $listing['Listing_ID']; ?>"
                                                    class="pill-button delete-button">Delete</a></td>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>You have no listings yet. <a href="create_listing.php" class="pill-button">Create one here</a>.</p>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>
</body>