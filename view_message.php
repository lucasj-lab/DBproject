<?php
require 'database_connection.php';

// Ensure the user is logged in
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
if ($message['Recipient_ID'] === $userId) {
    $updateQuery = "UPDATE messages SET Read_Status = 'read' WHERE Message_ID = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $messageId);
    $updateStmt->execute();
    $updateStmt->close();
}
?><?php
require 'database_connection.php';

// Ensure the user is logged in
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
if ($message['Recipient_ID'] === $userId) {
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
</head>
<body>
    <div class="message-container">
        <h2>Message Details</h2>
        <p><strong>From:</strong> <?= htmlspecialchars($message['Sender_Name']) ?> (<?= htmlspecialchars($message['Sender_Email']) ?>)</p>
        <p><strong>Subject:</strong> <?= htmlspecialchars($message['Subject']) ?></p>
        <p><strong>Received:</strong> <?= htmlspecialchars($message['Created_At']) ?></p>
        <p><strong>Message:</strong></p>
        <p><?= nl2br(htmlspecialchars($message['Message_Text'])) ?></p>
        <a href="messages.php?section=inbox" class="back-button">Back to Inbox</a>
    </div>
</body>
</html>
