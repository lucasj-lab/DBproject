<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
    
    <?php
session_start();
require 'database_connection.php'; // Ensure your database connection is included

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, date_joined FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch user's listings
$stmt = $conn->prepare("SELECT id, title, description, price, date_posted FROM listings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>


</head>
<body>

<!-- Header Section with Full Navbar and Hamburger Menu -->
<header>
    <div class="logo">
        <h1>User Dashboard</h1>
    </div>
    <nav>
        <ul class="desktop-menu">
            <li><a href="index.html">Home</a></li>
            <li><a href="create_listing.html">New Listing</a></li>
            <li><a href="listings.html">View All Listings</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <!-- User Icon -->
    <div class="user-icon">
        <a href="user_dashboard.php">U</a>
    </div>

    <!-- Hamburger menu for mobile view -->
    <div class="hamburger" onclick="toggleMobileMenu()">☰</div>
    <div class="mobile-menu" id="mobileMenu">
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="create_listing.html">New Listing</a></li>
            <li><a href="listings.html">View All Listings</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</header>

<main>
    <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?></h1>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Member Since:</strong> <?php echo htmlspecialchars($user['date_joined']); ?></p>

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
                        <td><?php echo htmlspecialchars($listing['title']); ?></td>
                        <td><?php echo htmlspecialchars($listing['description']); ?></td>
                        <td>$<?php echo htmlspecialchars($listing['price']); ?></td>
                        <td><?php echo htmlspecialchars($listing['date_posted']); ?></td>
                        <td>
                            <a href="edit_listing.php?id=<?php echo $listing['id']; ?>">Edit</a> |
                            <a href="delete_listing.php?id=<?php echo $listing['id']; ?>" onclick="return confirm('Are you sure you want to delete this listing?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have no listings yet. <a href="create_listing.html">Create one here</a>.</p>
    <?php endif; ?>
</main>

<script>
    function toggleMobileMenu() {
        document.getElementById("mobileMenu").classList.toggle("active");
    }
</script>
</body>
</html>
