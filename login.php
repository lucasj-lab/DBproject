<?php
session_start();
ob_start();
require 'database_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $stmt = $conn->prepare("SELECT * FROM user WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['is_admin'] = $user['is_admin'];

        if ($user['is_admin'] == 1) {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }
        exit();
    } else {
        $_SESSION['message'] = "Invalid email or password.";
        $_SESSION['message_type'] = 'error';
        header("Location: login.php");
        exit();
    }

    $stmt->close();
}
ob_end_flush();
