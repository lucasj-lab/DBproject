<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set default values for session variables to avoid undefined index warnings
$isAdmin = $_SESSION['is_admin'] ?? false; // Defaults to false if 'is_admin' is not set
$username = $_SESSION['name'] ?? 'User'; // Defaults to 'User' if 'username' is not set
?>

<header>
    <div class="logo">
        <h1><?php echo $isAdmin ? "Admin Dashboard" : "User Dashboard"; ?></h1>
    </div>
    <nav class="desktop-menu">
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="create_listing.html">New Listing</a></li>
            <li><a href="listings.html">View All Listings</a></li>
            <?php if ($isAdmin): ?>
                <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
            <?php else: ?>
                <li><a href="user_dashboard.php">User Dashboard</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <!-- User Icon -->
    <div class="user-icon">
        <a href="<?php echo $isAdmin ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>">
            <?php echo substr($username, 0, 1); // Display the first letter of the username or 'U' ?>
        </a>
    </div>

    <!-- Hamburger Menu for Mobile View -->
    <div class="hamburger" onclick="toggleMobileMenu()">☰</div>

    <!-- Mobile Dropdown Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="create_listing.html">New Listing</a></li>
            <li><a href="listings.html">View All Listings</a></li>
            <?php if ($isAdmin): ?>
                <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
            <?php else: ?>
                <li><a href="user_dashboard.php">User Dashboard</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</header>

<script>
    function toggleMobileMenu() {
        document.getElementById("mobileMenu").classList.toggle("active");
    }
</script>
