<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set default values for session variables to avoid undefined index warnings
$isAdmin = $_SESSION['is_admin'] ?? false; // Defaults to false if 'is_admin' is not set
$username = $_SESSION['name'] ?? 'User'; // Defaults to 'User' if 'username' is not set
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $isAdmin ? 'Admin Dashboard' : 'Rookielist'; ?></title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOMt23cez/3paNdF+K9aIIXUXl09Aq5AxlE9+y5T" crossorigin="anonymous">
</head>

<body>
  <header>
    <div class="logo">
      <h1><?php echo $isAdmin ? "Admin Dashboard" : "Rookielist"; ?></h1>
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
      <a href="<?php echo $isAdmin ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>" aria-label="User Dashboard">
        <i class="fas fa-user"></i> <!-- Font Awesome user icon -->
      </a>
      <span><?php echo htmlspecialchars(substr($username, 0, 1)); ?></span> <!-- First letter of the username or 'U' -->
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
