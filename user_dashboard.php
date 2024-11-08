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
$stmt = $conn->prepare("SELECT Name, Email, Date_Joined FROM user WHERE User_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch user's listings
$stmt = $conn->prepare("SELECT User_ID, Title, Description, Price, Date_Posted FROM listings WHERE User_ID = ?");
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

    <!-- Main Content -->
    <main class="dashboard-main">
        
        <!-- Personalized welcome message with user data -->
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
                                <a href="edit_listing.php?listing_id=<?php echo $listing['Listing_ID']; ?>"
                                    class="pill-button button-edit">Edit</a>
                                <a href="delete_listing.php?listing_id=<?php echo $listing['Listing_ID']; ?>"
                                    class="pill-button button-delete" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                </div>
            </table>

        <?php else: ?>
            <p>You have no listings yet. <a href="create_listing.php">Create one here</a>.</p>
        <?php endif; ?>
    </main>

    <!-- Footer Section -->
    <footer>
        <p>&copy; 2024 Rookies 2.0 | All rights reserved.</p>
        <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
    </footer>
</body>

</html>