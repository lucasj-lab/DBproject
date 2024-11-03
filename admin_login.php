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

    // Verify password and initialize session if login is successful
    if ($user && password_verify($password, $user['password'])) {
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
<body>
    <h2>Log In</h2>

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
