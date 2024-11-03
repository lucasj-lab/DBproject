<?php 
session_start();
ob_start();
require 'database_connection.php'; // Initializes $conn for MySQLi connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = isset($_POST['username']) ? htmlspecialchars(trim($_POST['username'])) : '';
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $date_joined = date('Y-m-d');

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['message'] = "All fields are required.";
        $_SESSION['message_type'] = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Invalid email format.";
        $_SESSION['message_type'] = 'error';
    } elseif (strlen($password) < 8) {
        $_SESSION['message'] = "Password must be at least 8 characters long.";
        $_SESSION['message_type'] = 'error';
    } else {
        // Check if email is already registered
        $stmt = $conn->prepare("SELECT * FROM user WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['message'] = "Email is already registered.";
            $_SESSION['message_type'] = 'error';
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO user (Username, Email, Password, Date_Joined) VALUES (?, ?, ?, ?)");
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $date_joined);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Registration successful! You can now log in.";
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = "Registration failed: " . $stmt->error;
                $_SESSION['message_type'] = 'error';
            }
        }

        $stmt->close();
    }

    // Redirect after form submission
    header("Location: login.php");
    exit();
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>User Registration</h1>
        <nav class="desktop-menu">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="create_listing.html">Create Listing</a></li>
                <li><a href="listings.html">View All Listings</a></li>
                <li><a href="login.php">Log In</a></li>
                <li><a href="about.html">About</a></li>
            </ul>
        </nav>
    
        <div class="user-icon">
            <a href="user_dashboard.php">U</a> <!-- Placeholder initial for the user icon -->
        </div>
    
        <div class="hamburger" onclick="toggleMobileMenu()">☰</div>
    
        <div class="mobile-menu" id="mobileMenu">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="create_listing.html">Create Listing</a></li>
                <li><a href="listings.html">Listings</a></li>
                <li><a href="login.php">Log In</a></li>
                <li><a href="about.html">About</a></li>
            </ul>
        </div>
    </header>

    <!-- JavaScript to toggle the mobile menu -->
    <script>
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById("mobileMenu");
            mobileMenu.classList.toggle("active");
        }
    </script>

    <div class="registration">
        <h2>Create an Account</h2>

        <!-- Display session messages if they exist -->
        <?php if (isset($_SESSION['message'])) : ?>
            <div class="message-box <?= ($_SESSION['message_type'] === 'success') ? 'success' : 'error'; ?>">
                <p><?= $_SESSION['message']; ?></p>
            </div>
            <?php 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']); 
            ?>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="register-fields">
                <input type="text" id="username" name="username" placeholder="Username" required>
                <input type="email" id="email" name="email" placeholder="Email" required>
                <input type="password" id="password" name="password" placeholder="Password" required
                       pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                       title="Must contain at least one number, one uppercase and lowercase letter, and at least 8 or more characters">
                <button type="submit">Register</button>
            </div>
        </form>
        
        <p>Already have an account? <a href="login.php">Log in here</a>.</p>
    </div>

    <footer>
        <p>&copy; 2024 Rookies 2.0 | All rights reserved.</p>
        <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
    </footer>

    <style>
        /* Add CSS as in the original code */
    </style>
</body>
</html>
