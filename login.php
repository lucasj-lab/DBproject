<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

<?php
session_start();
require 'database_connection.php';

$error_message = ""; // Initialize error message variable

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
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];


        // Set the success message
        $_SESSION['message'] = "You have successfully logged in.";
        $_SESSION['message_type'] = 'success';

        // Redirect based on admin status
        header("Location: " . ($user['is_admin'] ? "admin_dashboard.php" : "user_dashboard.php"));
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
    <title>User Log In</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <h2>User Log In</h2>

    <!-- Display error message if login fails -->
    <?php if (!empty($error_message)): ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="log-in-fields">
            <input type="email" name="email" placeholder="Enter your email address"
                value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Log In</button>
        </div>
    </form>

    <?php include 'footer.php'; ?>
</body>

</html>