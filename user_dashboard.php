<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);



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
    <header>
        <div class="logo">
            <h1>User Dashboard</h1>
        </div>

        <!-- Desktop Navigation Menu -->
        <nav>
            <ul class="desktop-menu">
                <li><a href="index.html">Home</a></li>
                <li><a href="create_listing.html">New Listing</a></li>
                <li><a href="listings.html">View All Listings</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>

        <!-- User Icon for Dashboard Access -->
        <div class="user-icon">
            <a href="user_dashboard.php">U</a> <!-- "U" as placeholder for the user icon -->
        </div>

        <!-- Hamburger Menu Icon for Mobile View -->
        <div class="hamburger" onclick="toggleMobileMenu()">☰</div>

        <!-- Mobile Dropdown Menu -->
        <div class="mobile-menu" id="mobileMenu">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="create_listing.html">New Listing</a></li>
                <li><a href="listings.html">View All Listings</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Personalized welcome message with user data -->
        <h1>Welcome, <?php echo htmlspecialchars($user['Name']); ?></h1>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
        <p><strong>Member Since:</strong> <?php echo htmlspecialchars($user['Date_Joined']); ?></p>

        <h2>Your Listings</h2>

        <?php if (!empty($listings)): ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Date Posted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listings as $listing): ?>
                <tr>
                <td><?php echo htmlspecialchars($listing['Title']); ?></td>
                <td><?php echo htmlspecialchars($listing['Description']); ?></td>
                <td>$<?php echo htmlspecialchars($listing['Price']); ?></td>
                <td><?php echo htmlspecialchars($listing['Date_Posted']); ?></td>

                    <td>
                        <a href="edit_listing.php?id=<?php echo $listing['Listing_id']; ?>">Edit</a> |
                        <a href="delete_listing.php?id=<?php echo $listing['Listing_id']; ?>"
                            onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>You have no listings yet. <a href="create_listing.html">Create one here</a>.</p>
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
