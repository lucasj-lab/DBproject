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

    // Check if user exists and if password field is set
    if ($user && isset($user['password']) && password_verify($password, $user['password'])) {
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
