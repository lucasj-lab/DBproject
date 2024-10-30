<?php
session_start(); // Start the session to access user information

$host = 'database-1.c5qwuo6qo0y3.us-east-2.rds.amazonaws.com';
$db   = 'new_craigslist_db';
$user = 'admin';
$pass = 'Imtheman198627*';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id']; // Assuming you store the user_id in session after registration
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $state = $_POST['state'];
    $city = $_POST['city'] ?: $_POST['city-input'];

    try {
        $stmt = $pdo->prepare("INSERT INTO listings (user_id, title, description, price, state, city) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $description, $price, $state, $city]);
        echo "Success! Your listing has been created.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
