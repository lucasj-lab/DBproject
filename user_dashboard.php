<?php
session_start();
require 'database_connection.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT Name, Email, Date_Joined FROM user WHERE User_ID = ?");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();  // Close the statement after use

// Fetch user's listings
$stmt = $pdo->prepare("SELECT Listing_ID, Title, Description, Price, Date_Posted FROM listings WHERE User_ID = ?");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->execute();
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();  // Close the statement after use

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

    <div class="logo">
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
        <p><strong>Member Since:</strong>
            <?php
            $dateJoined = new DateTime($user['Date_Joined']);
            echo htmlspecialchars($dateJoined->format('l, F jS, Y'));
            ?>
        </p>

        <h2>Your Listings</h2>

        <?php if (!empty($listings)): ?>
            <div class="table-container">
                <table class="dashboard-table">
                    <thead class="dashboard-header">
                        <tr>
                            <th class="dashboard-header-cell">Title</th>
                            <th class="dashboard-header-cell">Description</th>
                            <th class="dashboard-header-cell">Price</th>
                            <th class="dashboard-header-cell">Date Posted</th>
                            <th class="dashboard-header-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="dashboard-body">
                        <?php foreach ($listings as $listing): ?>
                            <tr class="dashboard-row">
                                <td class="dashboard-cell"><?php echo htmlspecialchars($listing['Title']); ?></td>
                                <td class="dashboard-cell"><?php echo htmlspecialchars($listing['Description']); ?></td>
                                <td class="dashboard-cell">$<?php echo htmlspecialchars($listing['Price']); ?></td>
                                <td class="dashboard-cell">
                                    <?php
                                    $datePosted = new DateTime($listing['Date_Posted']);
                                    echo htmlspecialchars($datePosted->format('l, F jS, Y'));
                                    ?>
                                </td>
                                <td class="dashboard-cell actions-cell">
                                    <a href="edit_listing.php?listing_id=<?php echo htmlspecialchars($listing['Listing_ID']); ?>"
                                        class="pill-button button-edit">Edit</a>
                                    <a href="delete_listing.php?listing_id=<?php echo htmlspecialchars($listing['Listing_ID']); ?>"
                                        class="pill-button button-delete" onclick="return confirm('Are you sure you want to delete this listing?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>You have no listings yet. <a href="create_listing.php">Create one here</a>.</p>
        <?php endif; ?>
    </main>

    <!-- Footer Section -->
    <footer>
        <p>&copy; 2029 Rookies 2.0 | All rights reserved.</p>
    </footer>
</body>

</html>
