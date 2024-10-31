<?php
session_start();

// Database connection parameters
$servername = "database-1-instance-1.cpgoq8m2kfkd.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "Bagflea3!";
$dbname = "CraigslistDB";
$charset = 'utf8mb4';

$dsn = "mysql:host=$servername;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $date_joined = date('Y-m-d');

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
        try {
            $stmt = $pdo->prepare("SELECT * FROM user WHERE Email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = "Email is already registered.";
                $_SESSION['message_type'] = 'error';
            } else {
                $stmt = $pdo->prepare("INSERT INTO user (Name, Email, Password, Date_Joined) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $date_joined]);
                $_SESSION['message'] = "Registration successful! You can now log in.";
                $_SESSION['message_type'] = 'success';
            }
        } catch (PDOException $e) {
            $_SESSION['message'] = "Error: " . htmlspecialchars($e->getMessage());
            $_SESSION['message_type'] = 'error';
        }
    }
    header("Location: registration.html");
    exit();
}
?>
