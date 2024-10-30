<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "database-1-instance-1.cpgoq8m2kfkd.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "Bagflea3!"; 
$dbname = "CraigslistDB";


// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
            $message = "Invalid password.";
            $message_type = "error";
        }
    } else {
        $message = "Email not found.";
        $message_type = "error";
    }
    $stmt->close();
}

$conn->close();
?>
