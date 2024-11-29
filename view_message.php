<?php
require 'database_connection.php';
session_start();

// Fetch the message ID from the URL
$messageId = intval($_GET['message_id'] ?? 0);

if (!$messageId) {
    die("Invalid message ID.");
}

// Mark the message as read
$updateQuery = "UPDATE messages SET Read_Status = 1 WHERE Message_ID = ?";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("i", $messageId);
$stmt->execute();
$stmt->close();

// Fetch the main message
$messageQuery = "SELECT messages.Message_Text, messages.Created_At, 
                        sender.Name AS Sender_Name, sender.User_ID AS Sender_ID 
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

// Fetch replies for the message
$repliesQuery = "SELECT replies.Reply_Text, replies.Created_At, sender.Name AS Sender_Name 
                 FROM replies
                 JOIN user AS sender ON replies.Sender_ID = sender.User_ID
                 WHERE replies.Message_ID = ?
                 ORDER BY replies.Created_At ASC";
$stmt = $conn->prepare($repliesQuery);
$stmt->bind_param("i", $messageId);
$stmt->execute();
$replies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
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
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="sidebar">
            <ul>
                <li><a href="messages.php?section=inbox">Inbox</a></li>
                <li><a href="messages.php?section=sent">Sent</a></li>
                <li><a href="messages.php?section=drafts">Drafts</a></li>
                <li><a href="messages.php?section=trash">Trash</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="message-container">
                <h2>Message Details</h2>
                <p><strong>From:</strong> <?php echo htmlspecialchars($message['Sender_Name']); ?></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($message['Created_At']); ?></p>
                <p><strong>Message:</strong></p>
                <p class="message-text"><?php echo nl2br(htmlspecialchars($message['Message_Text'])); ?></p>

                <div class="message-actions">
                    <button id="replyButton" class="btn" 
                            data-message-id="<?php echo $messageId; ?>" 
                            data-sender-id="<?php echo $message['Sender_ID']; ?>">Reply</button>
                    <button onclick="window.location.href='messages.php?section=inbox';" class="btn">Back to Messages</button>
                </div>
            </div>

            <div class="replies-container">
                <h3>Conversation</h3>
                <?php if (!empty($replies)): ?>
                    <?php foreach ($replies as $reply): ?>
                        <div class="reply-card">
                            <div class="reply-header">
                                <strong><?php echo htmlspecialchars($reply['Sender_Name']); ?></strong>
                                <span class="reply-date"><?php echo htmlspecialchars($reply['Created_At']); ?></span>
                            </div>
                            <div class="reply-body">
                                <p><?php echo nl2br(htmlspecialchars($reply['Reply_Text'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-replies">No replies yet. Be the first to reply!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

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

    <?php include 'footer.php'; ?>
    <script src="reply.js" defer></script>
</body>
</html>
