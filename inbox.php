<?php
session_start();
require 'database_connection.php';

$user_id = $_SESSION['user_id'];

// Fetch received messages
$receivedStmt = $pdo->prepare("
    SELECT m.Message_Text, m.Created_At, u.Name AS Sender_Name 
    FROM messages m
    JOIN user u ON m.Sender_ID = u.User_ID
    WHERE m.Recipient_ID = ?
    ORDER BY m.Created_At DESC
");
$receivedStmt->execute([$user_id]);
$receivedMessages = $receivedStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch sent messages
$sentStmt = $pdo->prepare("
    SELECT m.Message_Text, m.Created_At, u.Name AS Recipient_Name 
    FROM messages m
    JOIN user u ON m.Recipient_ID = u.User_ID
    WHERE m.Sender_ID = ?
    ORDER BY m.Created_At DESC
");
$sentStmt->execute([$user_id]);
$sentMessages = $sentStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Inbox</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="inbox-container">
        <h1>Your Inbox</h1>

        <h2>Received Messages</h2>
        <ul>
            <?php foreach ($receivedMessages as $message): ?>
                <li>
                    <strong>From:</strong> <?= htmlspecialchars($message['Sender_Name']) ?><br>
                    <strong>Message:</strong> <?= htmlspecialchars($message['Message_Text']) ?><br>
                    <strong>Time:</strong> <?= htmlspecialchars($message['Created_At']) ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <h2>Sent Messages</h2>
        <ul>
            <?php foreach ($sentMessages as $message): ?>
                <li>
                    <strong>To:</strong> <?= htmlspecialchars($message['Recipient_Name']) ?><br>
                    <strong>Message:</strong> <?= htmlspecialchars($message['Message_Text']) ?><br>
                    <strong>Time:</strong> <?= htmlspecialchars($message['Created_At']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
