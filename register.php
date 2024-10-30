<?php
$servername = "database-1-instance-1.cpgoq8m2kfkd.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "Bagflea3!";
$dbname = "CraigslistDB";
$charset = 'utf8mb4';

$dsn = "mysql:host=$servername;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$message = ''; // Variable to store feedback message
$message_type = ''; // Type of message (success or error)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $date_joined = date('Y-m-d'); // Current date

    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $message = "All fields are required.";
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $message_type = 'error';
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $message_type = 'error';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT * FROM user WHERE Email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $message = "Email is already registered. Please try logging in.";
                $message_type = 'error';
            } else {
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO user (Name, Email, Password, Date_Joined) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $date_joined]);
                $message = "Registration successful! You can now log in to create a listing.";
                $message_type = 'success';

                // Clear form values
                $_POST['name'] = '';
                $_POST['email'] = '';
                $_POST['password'] = '';
            }
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>User Registration</h1>
        <nav>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="post_ad.html">New Listing</a></li>
                <li><a href="login.html">Login</a></li>
                <li><a href="about.html">About</a></li>
            </ul>
        </nav>
    </header>

    <div class="registration">
        <h2>Create an Account</h2>

        <?php if (!empty($message)) : ?>
            <div class="message-box <?php echo $message_type; ?>">
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" class="input-field" value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="input-field" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" class="input-field" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" 
                title="Must contain at least one number, one uppercase and lowercase letter, and at least 8 or more characters">

            <button type="submit">Register</button>
        </form>

        <p>Already have an account? <a href="login.html">Log in here</a>.</p>
    </div>

    <footer>
        <p>&copy; 2024 Craigslist 2.0 | All rights reserved</p>
    </footer>
</body>
</html>
