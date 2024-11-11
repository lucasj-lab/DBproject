<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'database_connection.php';

$error_message = ""; // Initialize error message variable

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Basic validation
    if (empty($email) || empty($password)) {
        $error_message = "Email and password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        // Prepare and execute the query to fetch user details by email
        $stmt = $pdo->prepare("SELECT * FROM user WHERE Email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch the user data
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['Password'])) {
            // Successful login
            $_SESSION['user_id'] = $user['User_ID'];
            $_SESSION['message'] = "Login successful!";
            $_SESSION['message_type'] = 'success';
            header("Location: user_dashboard.php");
            exit();
        } else {
            // Invalid login credentials
            $error_message = "Incorrect email or password.";
        }

        $stmt->closeCursor(); // Close the statement after use
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
            </div>
        </form>
        <p>Wanna be a Rookie? </p>
        <a href="signup.php">Sign up here</a>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
