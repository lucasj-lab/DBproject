<?php
session_start();
require 'database_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, date_joined FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT id, title, description, price, date_posted FROM listings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <main>
        <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Member Since:</strong> <?php echo htmlspecialchars($user['date_joined']); ?></p>

        <h2>Your Listings</h2>
        <?php if (!empty($listings)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Date Posted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listings as $listing): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($listing['title']); ?></td>
                            <td><?php echo htmlspecialchars($listing['description']); ?></td>
                            <td>$<?php echo htmlspecialchars($listing['price']); ?></td>
                            <td><?php echo htmlspecialchars($listing['date_posted']); ?></td>
                            <td>
                                <a href="edit_listing.php?id=<?php echo $listing['id']; ?>">Edit</a> |
                                <a href="delete_listing.php?id=<?php echo $listing['id']; ?>"
                                    onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have no listings yet. <a href="create_listing.html">Create one here</a>.</p>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>
</body>

</html>