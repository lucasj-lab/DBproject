<?php
session_start();
require 'database_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirmPassword = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    $dateJoined = date('Y-m-d');

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION['message'] = "All fields are required.";
        $_SESSION['message_type'] = 'error';
        header("Location: signup.php");
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Invalid email format.";
        $_SESSION['message_type'] = 'error';
        header("Location: signup.php");
        exit();
    } elseif ($password !== $confirmPassword) {
        $_SESSION['message'] = "Passwords do not match.";
        $_SESSION['message_type'] = 'error';
        header("Location: signup.php");
        exit();
    }

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT * FROM user WHERE Email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['message'] = "Email is already registered.";
            $_SESSION['message_type'] = 'error';
            header("Location: signup.php");
            exit();
        }

        // Create a new user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO user (Name, Email, Password, Date_Joined, Is_Verified) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$name, $email, $hashed_password, $dateJoined]);

        // Generate a verification token
        $token = bin2hex(random_bytes(16));
        $userId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO email_verifications (User_ID, Token) VALUES (?, ?)");
        $stmt->execute([$userId, $token]);

        // Send verification email
        $verificationLink = "http://yourdomain.com/verify_email.php?token=$token";
        $subject = "Verify Your Email";
        $message = "Click the following link to verify your email: $verificationLink";
        mail($email, $subject, $message); // Ensure mail() is configured on your server

        $_SESSION['message'] = "Sign up successful! Please check your email to verify your account.";
        $_SESSION['message_type'] = 'success';
        header("Location: login.php");
        exit();
    } catch (Exception $e) {
        error_log("Error during signup: " . $e->getMessage());
        $_SESSION['message'] = "An error occurred. Please try again.";
        $_SESSION['message_type'] = 'error';
        header("Location: signup.php");
        exit();
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
    <?php include 'header.php'; ?>

    <div class="signup-container">
        <h2>Create an Account</h2>

        <!-- Display session messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message-box <?php echo ($_SESSION['message_type'] === 'success') ? 'success' : 'error'; ?>">
                <p><?php echo $_SESSION['message']; ?></p>
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
                <button type="submit">Sign Up</button>
                <p>Already have an account? <a href="login.php">Log in here</a>.</p>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
