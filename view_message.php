<?php
require 'database_connection.php';

$messageId = intval($_GET['message_id'] ?? 0);

if (!$messageId) {
    die("Invalid message ID.");
}

// Fetch the message details
$messageQuery = "SELECT messages.Subject, messages.Message_Text, messages.Created_At, sender.Name AS Sender_Name, sender.User_ID AS Sender_ID
                 FROM messages
                 JOIN user AS sender ON messages.Sender_ID = sender.User_ID
                 WHERE messages.Message_ID = ?";
$stmt = $conn->prepare($messageQuery);
$stmt->bind_param("i", $messageId);
$stmt->execute();
$result = $stmt->get_result();
$message = $result->fetch_assoc();
$stmt->close();

if (!$message) {
    die("Message not found.");
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
        <p><strong>From:</strong> <?php echo htmlspecialchars($message['Sender_Name']); ?></p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($message['Created_At']); ?></p>
        <p><strong>Subject:</strong> <?php echo htmlspecialchars($message['Subject']); ?></p>
        <p><strong>Message:</strong></p>
        <p><?php echo nl2br(htmlspecialchars($message['Message_Text'])); ?></p>
    </div>

    <!-- Reply Button -->
    <button id="replyButton" class="btn">Reply</button>

    <!-- Reply Modal -->
    <div id="replyModal" class="modal">
        <div class="modal-content">
            <span id="closeReplyModal" class="close-modal">&times;</span>
            <h3>Reply to Message</h3>
            <textarea id="replyText" class="reply-textarea" placeholder="Type your reply here..."></textarea>
            <div class="modal-actions">
                <button id="sendReply" class="btn send-btn">Send</button>
            </div>
        </div>
    </div>

    <script src="reply.js" defer></script>
</body>
</html>
