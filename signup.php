<?php
session_start();
require 'database_connection.php'; // Initializes $conn for MySQLi connection
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirmPassword = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    $dateJoined = date('Y-m-d');

    // Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION['message'] = "All fields are required.";
        $_SESSION['message_type'] = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Invalid email format.";
        $_SESSION['message_type'] = 'error';
    } elseif ($password !== $confirmPassword) {
        $_SESSION['message'] = "Passwords do not match.";
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
            // Insert new user3
            $stmt = $conn->prepare("INSERT INTO user (Name, Email, Password, Date_Joined) VALUES (?, ?, ?, ?)");
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $date_joined);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Sign up successful! You can now log in.";
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = "Sign up failed: " . $stmt->error;
                $_SESSION['message_type'] = 'error';
            }
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Sign up</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>


    <header>
        <?php include 'header.php'; ?>
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
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message-box <?= ($_SESSION['message_type'] === 'success') ? 'success' : 'error'; ?>">
                <p><?= $_SESSION['message']; ?></p>
            </div>
            <?php
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <form action="signup.php" method="POST">
            <div class="register-fields">
                <input type="text" id="name" name="name" placeholder="Name" required>
                <input type="email" id="email" name="email" placeholder="Email" required>
                <input type="password" id="password" name="password" placeholder="Password" required
                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                    title="Must contain at least one number, one uppercase and lowercase letter, and at least 8 or more characters">

                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password"
                    required title="Please re-enter your password to confirm.">
                <button type="submit">Sign up</button>
            </div>
        </form>

        <p>Already have an account? <a href="login.php">Log in here</a>.</p>
    </div>
    
    <footer>
        <?php include 'footer.php'; ?>
    </footer>


    <script>
        document.querySelector("form").addEventListener("submit", function (e) {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;

            if (password !== confirmPassword) {
                e.preventDefault(); // Prevent form submission
                alert("Passwords do not match. Please try again.");
            }
        });
    </script>

    <style>
        /* Styles for message display */
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
    </style>
</body>

</html>