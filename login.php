<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


session_start();
require 'database_connection.php';

$error_message = ""; // Initialize error message variable

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $stmt = $conn->prepare("SELECT * FROM user WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Ensure password field is not null
        if ($user['Password'] && password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['User_ID'];
            echo "Login successful!";
            // Redirect or proceed
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "Email not found.";
    

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

    <form action="login.php" method="POST">
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