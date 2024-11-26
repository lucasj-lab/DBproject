<?php
require 'database_connection.php';

$user_id = intval($_SESSION['user_id'] ?? 0);

// Ensure the user is logged in
if (!$user_id) {
    die("You must be logged in to view your inbox.");
}

// Fetch received messages
$receivedQuery = "
    SELECT m.Message_Text, m.Created_At, u.Name AS Sender_Name, l.Title AS Listing_Title, i.Image_URL AS Thumbnail_URL
    FROM messages m
    JOIN user u ON m.Sender_ID = u.User_ID
    LEFT JOIN listings l ON m.Listing_ID = l.Listing_ID
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE m.Recipient_ID = ?
    ORDER BY m.Created_At DESC
";

$receivedStmt = $conn->prepare($receivedQuery);
$receivedStmt->bind_param("i", $user_id);
$receivedStmt->execute();
$receivedMessages = $receivedStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$receivedStmt->close();

// Fetch sent messages
$sentQuery = "
    SELECT m.Message_Text, m.Created_At, u.Name AS Recipient_Name, l.Title AS Listing_Title, i.Image_URL AS Thumbnail_URL
    FROM messages m
    JOIN user u ON m.Recipient_ID = u.User_ID
    LEFT JOIN listings l ON m.Listing_ID = l.Listing_ID
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE m.Sender_ID = ?
    ORDER BY m.Created_At DESC
";

$sentStmt = $conn->prepare($sentQuery);
$sentStmt->bind_param("i", $user_id);
$sentStmt->execute();
$sentMessages = $sentStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$sentStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Inbox</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 10px;
        }
        .message-item {
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .message-list {
            list-style-type: none;
            padding: 0;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="inbox-container">
        <h1>Your Inbox</h1>

        <!-- Received Messages -->
        <h2>Received Messages</h2>
        <ul class="message-list">
            <?php foreach ($receivedMessages as $message): ?>
                <li class="message-item">
                    <strong>From:</strong> <?= htmlspecialchars($message['Sender_Name']) ?><br>
                    <?php if (!empty($message['Thumbnail_URL'])): ?>
                        <img src="<?= htmlspecialchars($message['Thumbnail_URL']) ?>" alt="Thumbnail" class="thumbnail">
                    <?php endif; ?>
                    <strong>Listing:</strong> <?= htmlspecialchars($message['Listing_Title'] ?? 'No Listing') ?><br>
                    <strong>Message:</strong> <?= htmlspecialchars($message['Message_Text']) ?><br>
                    <strong>Time:</strong> <?= htmlspecialchars($message['Created_At']) ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Sent Messages -->
        <h2>Sent Messages</h2>
        <ul class="message-list">
            <?php foreach ($sentMessages as $message): ?>
                <li class="message-item">
                    <strong>To:</strong> <?= htmlspecialchars($message['Recipient_Name']) ?><br>
                    <?php if (!empty($message['Thumbnail_URL'])): ?>
                        <img src="<?= htmlspecialchars($message['Thumbnail_URL']) ?>" alt="Thumbnail" class="thumbnail">
                    <?php endif; ?>
                    <strong>Listing:</strong> <?= htmlspecialchars($message['Listing_Title'] ?? 'No Listing') ?><br>
                    <strong>Message:</strong> <?= htmlspecialchars($message['Message_Text']) ?><br>
                    <strong>Time:</strong> <?= htmlspecialchars($message['Created_At']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
