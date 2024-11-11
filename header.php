<?php
// Sanitize session variables to prevent XSS
$isAdmin = $_SESSION['is_admin'] ?? false; // Defaults to false if 'is_admin' is not set
$username = htmlspecialchars($_SESSION['name'] ?? 'User'); // Defaults to 'User' if 'username' is not set
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $isAdmin ? 'Admin Dashboard' : 'Rookielist'; ?></title>
  <link rel="stylesheet" href="styles.css">
  <header>
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
  </header>
</head>

<body>
  <header>
    < class="logo">
      <h1><?php echo $isAdmin ? "Admin Dashboard" : "Rookielist"; ?></h1>
</div>

    <!-- Mobile Dropdown Menu -->
    <div class="mobile-menu" id="mobileMenu">
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="create_listing.php">New Listing</a></li>
        <li><a href="listings.php">View All Listings</a></li>
        <?php if ($isAdmin): ?>
          <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
        <?php else: ?>
          <li><a href="user_dashboard.php">User Dashboard</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Logout</a></li>
      </ul>
      
      <div class="hamburger" onclick="toggleMobileMenu()">☰</div>

      <nav class="desktop-menu">
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="create_listing.php">New Listing</a></li>
        <li><a href="listings.php">View All Listings</a></li>
        <?php if ($isAdmin): ?>
          <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
        <?php else: ?>
          <li><a href="user_dashboard.php">User Dashboard</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Logout</a></li>
      </ul>
    </nav>
    
    </div>
  </header>

  <script>
    function toggleMobileMenu() {
      document.getElementById("mobileMenu").classList.toggle("active");
    }
  </script>

</body>
</html>
