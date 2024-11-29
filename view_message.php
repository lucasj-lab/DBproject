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
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 1.5rem;
            color: #333;
            cursor: pointer;
        }

        .reply-textarea {
            width: 100%;
            height: 100px;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
        }

        .modal-actions {
            display: flex;
            justify-content: space-between;
        }

        .modal-actions .btn {
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            border: none;
        }

        .send-btn {
            background-color: #007bff;
            color: #fff;
        }

        .send-btn:hover {
            background-color: #0056b3;
        }

        .draft-btn {
            background-color: #6c757d;
            color: #fff;
        }

        .draft-btn:hover {
            background-color: #5a6268;
        }
    </style>
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
