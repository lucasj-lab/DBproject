<?php
session_start();
require 'database_connection.php'; // Ensure this includes your database connection as $conn

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute the query to fetch user details by email
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check if user exists and verify password
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        // Redirect based on admin status
        if ($user['is_admin'] == 1) {
            header("Location: admin_dashboard.php"); // Redirect to admin dashboard
        } else {
            header("Location: user_dashboard.php"); // Redirect to user dashboard
        }
        exit();
    } else {
        $error_message = "Invalid email or password.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($error_message)): ?>
        <p><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
