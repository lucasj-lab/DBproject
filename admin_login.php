<?php
session_start();
require 'database_connection.php'; // Ensure this includes your MySQLi connection setup with $conn

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepare and execute the query to fetch user details by email
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check if the user exists and if the password matches
    if ($user && isset($user['password']) && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];

        // Redirect based on admin status
        if ($user['is_admin']) {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }
        exit();
    } else {
        $error_message = "Invalid email or password.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
</head>
<header>
        <div class="logo">
            <h1>Admin</h1>
        </div>
    
        <!-- Desktop Navigation Menu -->
        <nav>
            <ul class="desktop-menu">
                <li><a href="index.html">Home</a></li>
                <li><a href="create_listing.html">Create Listing</a></li>
                <li><a href="listings.html">View All Listings</a></li>
                <li><a href="register.html">Register</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    
        <!-- User Icon -->
        <div class="user-icon">
            <a href="user_dashboard.php">U</a> <!-- "U" as placeholder, replace with initials if needed -->
        </div>
    
        <!-- Hamburger Menu Icon for Mobile View -->
        <div class="hamburger" onclick="toggleMobileMenu()">☰</div>
    
        <!-- Mobile Dropdown Menu -->
        <div class="mobile-menu" id="mobileMenu">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="create_listing.html">Create Listing</a></li>
                <li><a href="listings.html">View All Listings</a></li>
                <li><a href="register.html">Register</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </header>
<body>
    <h2>Admin Log In</h2>

    <!-- Display error message if login fails -->
    <?php if (isset($error_message)): ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <form action="admin_login.php" method="POST">
        <div class="log-in-fields">
            <input type="email" id="email" name="email" placeholder="Enter your email address" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <button type="submit">Log In</button>
        </div>
    </form>
    
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
            background-color: #f5f5f5;
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
            margin: 1rem 0;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .listing-item h3 {
            margin: 0.5rem 0;
        }
    </style>
</body>
</html>
