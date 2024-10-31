<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "database-1-instance-1.cpgoq8m2kfkd.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "Bagflea3!";
$dbname = "CraigslistDB";

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    $_SESSION['message'] = "Connection failed: " . $conn->connect_error;
    $_SESSION['message_type'] = "error";
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verify user credentials
    $stmt = $conn->prepare("SELECT User_ID, Password FROM user WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Set session for logged-in user
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;
            header("Location: account.php");
            exit();
        } else {
            $_SESSION['message'] = "Invalid password.";
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Email not found.";
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
    header("Location: login.html");
    exit();
}

$conn->close();
?>
