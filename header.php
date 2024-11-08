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
</head>

<body>
  <header>
    <div class="logo">
      <h1><?php echo $isAdmin ? "Admin Dashboard" : "Rookielist"; ?></h1>
    </div>

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

  

    <div class="hamburger" onclick="toggleMobileMenu()">☰</div>

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
    </div>
  </header>

  <script>
    function toggleMobileMenu() {
      document.getElementById("mobileMenu").classList.toggle("active");
    }
  </script>

    <!-- Add styles and hamburger CSS -->
    <style>
        /* General Reset and Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #fff;
            color: #333;
        }

        header {
            background-color: #1a73e8;
            color: #fff;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .logo h1 {
            margin-left: 3em;
            font-size: 1.8rem;

        }

        .desktop-menu {
            display: flex;
            list-style: none;
        }

        .desktop-menu li {
            margin-left: 1rem;
        }

        .desktop-menu li a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }

        /* Hamburger Menu for Mobile */
        .hamburger {
            display: none;
            font-size: 1.8rem;
            color: #fff;
            cursor: pointer;
        }

        .mobile-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: #1a73e8;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0.2rem 0.2rem 0.5rem rgba(0, 0, 0, 0.1);
        }

        .mobile-menu.active {
            display: block;
        }

        .mobile-menu ul {
            display: flex;
            flex-direction: column;
            list-style: none;
        }

        .mobile-menu ul li {
            margin-bottom: 1rem;
        }

        .mobile-menu ul li a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .desktop-menu {
                display: none;
            }

            .hamburger {
                display: block;
            }
        }

        /* Listings Section */
        .listing-item {
            background-color: #fff;
            padding: 1rem;
            margin: 2rem 1rem;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .listing-item h3 {
            margin: 0.5rem 0;
        }
    </style>

    </body>
    </html>