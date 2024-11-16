<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
} // Start or continue the session

// Check if the user is logged in and if they are an admin
$isLoggedIn = isset($_SESSION['user_id']);
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
</head>

<body>
  <header>
    <div class="logo">
      <h1><?php echo $isAdmin ? "Admin Dashboard" : "Rookielist"; ?></h1>
    </div>

    <nav class="desktop-menu">
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="listings.php">View All Listings</a></li>
        
        <?php if ($isLoggedIn): ?>
          <li><a href="create_listing.php">New Listing</a></li>
          <?php if ($isAdmin): ?>
            <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
          <?php else: ?>
            <li><a href="user_dashboard.php">User Dashboard</a></li>
          <?php endif; ?>
          <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
          <li><a href="login.php">Login</a></li>
          <li><a href="signup.php">Signup</a></li>
        <?php endif; ?>
      </ul>
    </nav>

   <!-- User Icon -->
<div class="user-icon" id="userIcon">
    <a href="<?php echo $isLoggedIn ? 'user_dashboard.php' : 'login.php'; ?>">
        <img src="images/user-icon-white-black-back.svg" alt="User Icon">
    </a>
</div>


    <div class="hamburger" onclick="toggleMobileMenu()">☰</div>

    <!-- Mobile Dropdown Menu -->
    <div class="mobile-menu" id="mobileMenu">
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="listings.php">View All Listings</a></li>
        
        <?php if ($isLoggedIn): ?>
          <li><a href="create_listing.php">New Listing</a></li>
          <?php if ($isAdmin): ?>
            <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
          <?php else: ?>
            <li><a href="user_dashboard.php">User Dashboard</a></li>
          <?php endif; ?>
          <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
          <li><a href="login.php">Login</a></li>
          <li><a href="signup.php">Signup</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </header>

  <!-- JavaScript -->
  <script>
    // Toggle mobile menu visibility
    function toggleMobileMenu() {
      document.getElementById("mobileMenu").classList.toggle("active");
    }

    // Change user icon border color when logged in
    document.addEventListener("DOMContentLoaded", function() {
      const userIcon = document.getElementById("userIcon");
      const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
      if (isLoggedIn) {
        userIcon.classList.add("logged-in"); // Adds the class for styling if logged in
      }
    });
  </script>

  <style>
    /* CSS to set the user icon border color when logged in */
    .user-icon.logged-in {
      border: 2px solid green; /* Modify this style as per your requirements */
    }
  </style>

</body>
</html>
