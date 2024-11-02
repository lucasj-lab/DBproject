<?php
session_start();

// Ensure only admins can access this page
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: admin_login.php");
    exit();
}

require 'database_connection.php'; // Include your database connection file

// Fetch all users
$stmt = $pdo->prepare("SELECT id, username, email, date_joined, is_admin FROM users");
$stmt->execute();
$users = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - All Users</title>
</head>
<body>
    <h2>All Users</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Date Joined</th>
            <th>Admin Status</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['date_joined']); ?></td>
                <td><?php echo $user['is_admin'] ? 'Admin' : 'User'; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
