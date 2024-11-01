<?php
// Start session and output buffering at the top of the file
session_start();
ob_start();

// Database connection settings
$servername = "database-1-instance-1.cpgoq8m2kfkd.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "Bagflea3!";
$dbname = "CraigslistDB";

$dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Use isset to check if form fields are set, and trim the inputs
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
        try {
            // Check if email is already registered
            $stmt = $pdo->prepare("SELECT * FROM user WHERE Email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = "Email is already registered.";
                $_SESSION['message_type'] = 'error';
            } else {
                // Insert new user
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

    // Redirect after form submission
    header("Location: register.html");
    exit();
}

// Flush the output buffer
ob_end_flush();
?>
