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
<style>
    .session-message {
    margin: 20px auto;
    padding: 10px 20px;
    width: 80%;
    text-align: center;
    border-radius: 5px;
    font-size: 1rem;
}

.session-message.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    width: 100%;
}

.session-message.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    width: 100%;
}

</style>
<body>

<?php include 'header.php'; ?>
<?php include 'session_message.php'; ?>


    <div class="login-container">
        <h2>User Login</h2>

        <!-- Display error message if login fails -->
        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="log-in-fields">
                <input type="email" name="email" placeholder="Email address"
                    value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
                <p>Not signed up? <a href="signup.php">Sign up here</a></p>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
