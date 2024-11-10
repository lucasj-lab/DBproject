<?php
session_start();
require 'database_connection.php'; // Include your PDO connection setup here

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

// Fetch user's listings with associated images, city, and state
$sql = "SELECT listings.Listing_ID, listings.title, listings.description, listings.price, listings.date_posted, 
               listings.city, listings.state, images.image_url
        FROM listings 
        LEFT JOIN images ON listings.Listing_ID = images.Listing_ID
        WHERE listings.user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);


// Organize listings with their associated images
$listings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (!isset($listings[$row['Listing_ID']])) {
        $listings[$row['Listing_ID']]['details'] = [
            'title' => $row['title'],
            'description' => $row['description'],
            'price' => $row['price'],
            'date_posted' => $row['date_posted'],
            'city' => $row['city'],
            'state' => $row['state']
        ];
    }
    $listings[$row['Listing_ID']]['images'][] = $row['Image_URL'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function toggleMobileMenu() {
            document.getElementById("mobileMenu").classList.toggle("active");
        }
    </script>
</head>
<body>
    <!-- Header Section with Full Navigation Menu and User Icon -->
    <?php include 'header.php'; ?>

    <div class="dashboard-header">
        <h1>User Dashboard</h1>
    </div>

    <!-- Display success message if provided in the URL -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="dashboard-main">
        <h1 class="welcome-heading">Welcome, <?php echo htmlspecialchars($user['Name']); ?></h1>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
        <p><strong>Member Since:</strong> <?php echo htmlspecialchars((new DateTime($user['Date_Joined']))->format('l, F jS, Y')); ?></p>

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
                            <th>Images</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listings as $listing_id => $listing): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($listing['details']['title']); ?></td>
                                <td><?php echo htmlspecialchars($listing['details']['description']); ?></td>
                                <td>$<?php echo htmlspecialchars($listing['details']['price']); ?></td>
                                <td><?php echo htmlspecialchars((new DateTime($listing['details']['date_posted']))->format('l, F jS, Y')); ?></td>
                                <td><?php echo htmlspecialchars($listing['details']['city']); ?></td>
                                <td><?php echo htmlspecialchars($listing['details']['state']); ?></td>
                                <td>
                                    <?php if (!empty($listing['images'])): ?>
                                        <?php foreach ($listing['images'] as $image): ?>
                                            <?php if ($image): ?>
                                                <img src="<?php echo htmlspecialchars($image); ?>" alt="Listing Image" class="listing-image" style="width: 80px; height: auto; margin: 5px;">
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p>No images available</p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_listing.php?id=<?php echo $listing_id; ?>" class="pill-button-edit">Edit</a> |
                                    <a href="delete_listing.php?id=<?php echo $listing_id; ?>" class="pill-button-delete" onclick="return confirm('Are you sure?')">Delete</a>
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

    <!-- Footer Section -->
    <footer>
    <?php include 'footer.php'; ?>
    </footer>
</body>
</html>

