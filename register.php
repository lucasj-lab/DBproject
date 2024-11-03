<?php
session_start();
ob_start();
require 'database_connection.php'; // Initializes $conn for MySQLi connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $date_joined = date('Y-m-d');

    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['message'] = "All fields are required.";
        $_SESSION['message_type'] = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Invalid email format.";
        $_SESSION['message_type'] = 'error';
    } elseif (strlen($password) < 8) {
        $_SESSION['message'] = "Password must be at least 8 characters long.";
        $_SESSION['message_type'] = 'error';
    } else {
        // Check if email is already registered
        $stmt = $conn->prepare("SELECT * FROM user WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['message'] = "Email is already registered.";
            $_SESSION['message_type'] = 'error';
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO user (Name, Email, Password, Date_Joined) VALUES (?, ?, ?, ?)");
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $date_joined);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Registration successful! You can now log in.";
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = "Registration failed: " . $stmt->error;
                $_SESSION['message_type'] = 'error';
            }
        }

        $stmt->close();
    }

    // Redirect after form submission
    header("Location: register.login.php");
    exit();
}
ob_end_flush();
