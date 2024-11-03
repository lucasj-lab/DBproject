<?php
session_start();
ob_start();
require 'database_connection.php'; // Ensure this file initializes $conn for MySQLi connection

// Display session messages, if any
if (isset($_SESSION['message'])): ?>
    <div class="alert <?= $_SESSION['message_type']; ?>">
        <?= $_SESSION['message']; ?>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    </div>
<?php endif;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Prepare and execute the query to fetch user details by email
    $stmt = $conn->prepare("SELECT * FROM user WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check if user exists and if the password matches
    if ($user && password_verify($password, $user['Password'])) {
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
        $_SESSION['message'] = "Invalid email or password.";
        $_SESSION['message_type'] = 'error';
        header("Location: login.php"); // Redirect to login page to display the error
        exit();
    }

    $stmt->close();
    $conn->close();
}

ob_end_flush();
