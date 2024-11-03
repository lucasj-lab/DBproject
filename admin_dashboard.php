<?php
session_start();
require 'database_connection.php'; // Ensure this includes your MySQLi connection setup with $conn

// Ensure only admins can access this page
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all users
$stmt = $conn->prepare("SELECT id, username, email, date_joined, is_admin FROM user");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch all listings with user info
$stmt = $conn->prepare("SELECT listings.id, title, description, price, date_posted, user.username 
                        FROM listings 
                        JOIN user ON listings.user_id = user.id");
$stmt->execute();
$listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
    <script>
        function toggleMobileMenu() {
            document.getElementById("mobileMenu").classList.toggle("active");
        }
    </script>
</head>
<body>

<!-- Header Section with Full Navigation Menu -->
<header>
    <div class="logo">
        <h1>Admin Dashboard</h1>
    </div>
    <nav>
        <ul class="desktop-menu">
            <li><a href="index.html">Home</a></li>
            <li><a href="create_listing.html">New Listing</a></li>
            <li><a href="listings.html">View All Listings</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <!-- Admin Icon for Dashboard Access -->
    <div class="user-icon">
        <a href="admin_dashboard.php">A</a> <!-- "A" as placeholder for admin icon -->
    </div>

    <!-- Hamburger Menu for Mobile View -->
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
    <h1>Welcome, Admin</h1>

    <section>
        <h2>All Users</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Date Joined</th>
                    <th>Admin Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['date_joined']); ?></td>
                        <td><?php echo $user['is_admin'] ? 'Admin' : 'User'; ?></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>">Edit</a> |
                            <a href="delete_user.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section>
        <h2>All Listings</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Date Posted</th>
                    <th>Posted By</th>
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
                        <td><?php echo htmlspecialchars($listing['username']); ?></td>
                        <td>
                            <a href="edit_listing.php?id=<?php echo $listing['id']; ?>">Edit</a> |
                            <a href="delete_listing.php?id=<?php echo $listing['id']; ?>" onclick="return confirm('Are you sure you want to delete this listing?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

</body>
</html>
