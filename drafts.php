<?php
require 'database_connection.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view drafts.");
}

$userId = intval($_SESSION['user_id']);

// Fetch drafts for the logged-in user
$query = "SELECT * FROM drafts WHERE User_ID = ? ORDER BY Created_At DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$drafts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drafts</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Your Drafts</h1>
    <ul>
        <?php foreach ($drafts as $draft): ?>
            <li>
                <p><strong>Subject:</strong> <?= htmlspecialchars($draft['Subject'] ?: 'No Subject') ?></p>
                <p><strong>Message:</strong> <?= nl2br(htmlspecialchars($draft['Message_Text'])) ?></p>
                <p><strong>Created:</strong> <?= htmlspecialchars($draft['Created_At']) ?></p>
                <a href="reply_message.php?draft_id=<?= $draft['Draft_ID'] ?>">Edit</a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
