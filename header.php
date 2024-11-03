<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<header>
    <div class="logo">
        <h1><?php echo isset($_SESSION['is_admin']) && $_SESSION['is_admin'] ? "Admin Dashboard" : "User Dashboard"; ?></h1>
    </div>
    <nav class="desktop-menu">
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="create_listing.html">New Listing</a></li>
            <li><a href="listings.html">View All Listings</a></li>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
            <?php else: ?>
                <li><a href="user_dashboard.php">User Dashboard</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <!-- User Icon -->
    <div class="user-icon">
        <a href="<?php echo $_SESSION['is_admin'] ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>">
            <?php echo isset($_SESSION['username']) ? substr($_SESSION['username'], 0, 1) : 'U'; ?>
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
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
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
