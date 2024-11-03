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
</body>
</html>
