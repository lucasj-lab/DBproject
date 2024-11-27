<?php
require 'database_connection.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view messages.");
}

$messageId = intval($_GET['message_id'] ?? 0);

// Validate message ID
if (!$messageId) {
    echo "<p>Invalid message ID.</p>";
    exit;
}

// Fetch the message
$query = "
    SELECT m.Subject, m.Message_Text, m.Created_At, u.Name AS Sender_Name, u.Email AS Sender_Email
    FROM messages m
    JOIN user u ON m.Sender_ID = u.User_ID
    WHERE m.Message_ID = ? AND (m.Recipient_ID = ? OR m.Sender_ID = ?)
";

$stmt = $conn->prepare($query);
$userId = intval($_SESSION['user_id']);
$stmt->bind_param("iii", $messageId, $userId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>Message not found or you do not have permission to view this message.</p>";
    exit;
}

$message = $result->fetch_assoc();

// Mark the message as read if the user is the recipient
if ($message['Sender_ID'] !== $userId) {
    $updateQuery = "UPDATE messages SET Read_Status = 'read' WHERE Message_ID = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $messageId);
    $updateStmt->execute();
    $updateStmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .message-container {
            width: 70%;
            max-width: 800px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #062247;
            margin-bottom: 10px;
        }
        h3 {
            color: #444;
            margin-bottom: 15px;
        }
        p {
            font-size: 16px;
            color: #555;
            line-height: 1.6;
        }
        .message-footer {
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #062247;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #444;
        }
    </style>
</head>
<body>
    <div class="message-container">
        <h2>From: <?= htmlspecialchars($message['Sender_Name']) ?></h2>
        <h3>Subject: <?= htmlspecialchars($message['Subject']) ?></h3>
        <p><?= nl2br(htmlspecialchars($message['Message_Text'])) ?></p>
        <p><strong>Received:</strong> <?= htmlspecialchars($message['Created_At']) ?></p>

        <div class="message-footer">
            <a href="messages.php?section=inbox" class="btn">Back to Inbox</a>
        </div>
    </div>
</body>
</html>
