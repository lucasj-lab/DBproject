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
                <input type="text" id="name" name="name" placeholder="Name" required>
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
        /* General Reset and Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; color: #333; }
        
        /* Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #1a73e8;
            color: #fff;
            padding: 1rem;
            position: relative;
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

        /* Mobile Menu */
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
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .mobile-menu.active { display: block; }
        .mobile-menu ul { display: flex; flex-direction: column; }
        .mobile-menu ul li { margin-bottom: 1rem; }

        /* Responsive Design */
        @media (max-width: 768px) {
            .desktop-menu { display: none; }
            .hamburger { display: block; }
        }

        .registration, footer { text-align: center; margin: 2rem auto; }
        .register-fields input, .register-fields button { display: block; margin: 0.5rem auto; padding: 0.5rem; width: 80%; max-width: 300px; }
        .message-box { padding: 1rem; margin: 1rem auto; border-radius: 5px; width: 80%; max-width: 400px; color: #fff; text-align: center; }
        .success { background-color: #4CAF50; }
        .error { background-color: #f44336; }
    </style>
</body>
</html>
