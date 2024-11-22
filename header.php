<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
} // Start or continue the session

// Check if the user is logged in and if they are an admin
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $_SESSION['is_admin'] ?? false; // Defaults to false if 'is_admin' is not set
$username = htmlspecialchars($_SESSION['name'] ?? 'User'); // Defaults to 'User' if 'username' is not set

$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php'); // Get the file name without extension

// Map page names to their respective titles
$pageTitles = [
    'index' => 'Rookielist',
    'listings' => 'Listings',
    'user_dashboard' => 'Dashboard',
    'admin_dashboard' => 'Admin Dashboard',
    'create_listing' => 'Create Listing',
    'login' => 'Login',
    'signup' => 'Signup'
];

// Set the page title or default to 'Rookielist' if the page isn't mapped
$logoTitle = $pageTitles[$currentPage] ?? 'Rookielist';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($logoTitle); ?></title>
  <link rel="stylesheet" href="styles.css">
</head>

<body>
  <header>
    <div class="logo">
      <h1><?php echo htmlspecialchars($logoTitle); ?></h1>
      <a href="index.php"></a>
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

    <div class="user-icon" id="userIcon">
      <a href="<?php echo $isLoggedIn ? 'user_dashboard.php' : 'login.php'; ?>">
          <img src="images/user-icon-white-black-back.svg" alt="User Icon">
      </a>
    </div>

    <div class="hamburger" onclick="toggleMobileMenu()">☰</div>

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

  <!-- Logout Confirmation Modal -->
<div id="logoutModal" class="modal">
  <div class="modal-content">
    <p>Are you sure you want to log out?</p>
    <button id="confirmLogout" class="pill-button">Yes, Log Out</button>
    <button id="cancelLogout" class="pill-button">Cancel</button>
  </div>
</div>
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

    const logoutModal = document.getElementById('logoutModal');
    const confirmLogoutButton = document.getElementById('confirmLogout');
    const cancelLogoutButton = document.getElementById('cancelLogout');

    // Function to show the logout modal
    function showLogoutModal() {
        logoutModal.style.display = 'block';
    }

    // Cancel button logic to close the modal
    cancelLogoutButton.onclick = () => {
        logoutModal.style.display = 'none';
    };

    // Confirm button logic to log out the user
    confirmLogoutButton.onclick = () => {
        window.location.href = 'logout.php'; // Redirect to the logout page
    };

    // Close modal if user clicks outside the modal content
    window.onclick = (event) => {
        if (event.target === logoutModal) {
            logoutModal.style.display = 'none';
        }
    };
  </script>

  <style>
    /* CSS to set the user icon border color when logged in */
    .user-icon.logged-in {
      border: 2px solid green; /* Modify this style as per your requirements */
    }
  </style>
</body>
</html>


  


