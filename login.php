<?php
session_start();
ob_start();
require 'database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $stmt = $conn->prepare("SELECT * FROM user WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['is_admin'] = $user['is_admin'];

        if ($user['is_admin'] == 1) {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }
        exit();
    } else {
        $_SESSION['message'] = "Invalid email or password.";
        $_SESSION['message_type'] = 'error';
        header("Location: login.php");
        exit();
    }

    $stmt->close();
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Log In</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <h1>Log In</h1>
        </div>
    
        <!-- Desktop Navigation Menu -->
        <nav class="desktop-menu">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="create_listing.html">Create Listing</a></li>
                <li><a href="listings.html">View All Listings</a></li>
                <li><a href="register.php">Register</a></li>
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
                <li><a href="register.php">Register</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </header>
    
    <!-- JavaScript function for mobile menu toggle -->
    <script>
        function toggleMobileMenu() {
            document.getElementById("mobileMenu").classList.toggle("active");
        }
    </script>

    <div class="login">
        <h2>Log In</h2>

        <?php if (isset($_SESSION['message'])) : ?>
            <div class="message-box <?= htmlspecialchars($_SESSION['message_type']); ?>">
                <p><?= htmlspecialchars($_SESSION['message']); ?></p>
            </div>
            <?php 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="log-in-fields">
                <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <button type="submit">Log In</button>
            </div>
        </form>

        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>

    <footer>
        <p>&copy; 2024 Rookielist 2.0 | All rights reserved.</p>
        <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
    </footer>

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

        .desktop-menu ul, .mobile-menu ul {
            list-style: none;
        }

        .desktop-menu ul {
            display: flex;
        }

        .desktop-menu li {
            margin-left: 1rem;
        }

        .desktop-menu li a, .mobile-menu li a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }

        .user-icon {
            display: flex;
            align-items: center;
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
        }

        .mobile-menu ul li {
            margin-bottom: 1rem;
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

        .login {
            text-align: center;
            margin: 2rem auto;
        }

        .log-in-fields input, .log-in-fields button {
            display: block;
            margin: 0.5rem auto;
            padding: 0.5rem;
            width: 80%;
            max-width: 300px;
        }

        .message-box {
            padding: 1rem;
            margin: 1rem auto;
            border-radius: 5px;
            width: 80%;
            max-width: 400px;
            color: #fff;
            text-align: center;
        }

        .success {
            background-color: #4CAF50;
        }

        .error {
            background-color: #f44336;
        }

        footer {
            background-color: #333;
            color: #fff;
            padding: 1rem;
            text-align: center;
        }

        .footer-links a {
            color: #fff;
            text-decoration: none;
            margin: 0 0.5rem;
        }
    </style>
</body>
</html>
