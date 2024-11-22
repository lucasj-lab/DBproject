<?php
session_start();
require 'database_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Safely retrieve and sanitize user input
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Check if fields are empty
    if (empty($email) || empty($password)) {
        $_SESSION['message'] = "Email and password are required.";
        $_SESSION['message_type'] = 'error';
        header("Location: login.php");
        exit();
    }

    try {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT * FROM user WHERE Email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['Password'])) {
            // Password is correct; log the user in
            $_SESSION['user_id'] = $user['User_ID'];
            $_SESSION['user_name'] = $user['Name'];
            $_SESSION['user_logged_in'] = true; // Added flag for login state
            $_SESSION['message'] = "Login successful!";
            $_SESSION['message_type'] = 'success';
            header("Location: user_dashboard.php");
            exit();
        } else {
            // Invalid login
            $_SESSION['message'] = "Invalid email or password.";
            $_SESSION['message_type'] = 'error';
            header("Location: login.php");
            exit();
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['message'] = "An error occurred. Please try again.";
        $_SESSION['message_type'] = 'error';
        header("Location: login.php");
        exit();
    }
}
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

<?php include 'header.php'; ?>
<?php include 'session_message.php'; ?>


    <div class="login-container">
        <h2>User Log In</h2>

        <!-- Display error message if login fails -->
        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="log-in-fields">
                <input type="email" name="email" placeholder="Enter your email address"
                    value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Log In</button>
                <p>Wanna be a Rookie? <a href="signup.php">Sign up here</a></p>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
