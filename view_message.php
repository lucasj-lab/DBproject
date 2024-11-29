<?php
require 'database_connection.php';

$messageId = intval($_GET['message_id'] ?? 0);

if (!$messageId) {
    die("Invalid message ID.");
}

// Fetch the message details
try {
    $messageQuery = "SELECT 
                         messages.Subject, 
                         messages.Message_Text, 
                         messages.Created_At, 
                         sender.Name AS Sender_Name 
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
} catch (Exception $e) {
    die("Error fetching message: " . htmlspecialchars($e->getMessage()));
}

// Fetch replies (your provided code snippet)
try {
    $query = "SELECT 
                  replies.Reply_ID, 
                  replies.Reply_Text, 
                  replies.Created_At, 
                  sender.Name AS Sender_Name 
              FROM replies
              JOIN user AS sender ON replies.Sender_ID = sender.User_ID
              WHERE replies.Message_ID = ?
              ORDER BY replies.Created_At ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $messageId);
    $stmt->execute();
    $result = $stmt->get_result();

    $replies = [];
    while ($row = $result->fetch_assoc()) {
        $replies[] = $row;
    }

    $stmt->close();
} catch (Exception $e) {
    echo "<p>Error fetching replies: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
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

    <div class="replies-container">
        <h3>Replies</h3>
        <?php if (!empty($replies)): ?>
            <?php foreach ($replies as $reply): ?>
                <div class="reply">
                    <p><strong><?php echo htmlspecialchars($reply['Sender_Name']); ?>:</strong> 
                    <?php echo nl2br(htmlspecialchars($reply['Reply_Text'])); ?></p>
                    <p><em>Sent at: <?php echo htmlspecialchars($reply['Created_At']); ?></em></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No replies yet.</p>
        <?php endif; ?>
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

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const replyModal = document.getElementById('replyModal');
            const closeReplyModal = document.getElementById('closeReplyModal');
            const replyButton = document.getElementById('replyButton'); // Button to open modal
            const sendReplyButton = document.getElementById('sendReply'); // Button to send reply
            const replyText = document.getElementById('replyText'); // Textarea for the reply

            // Open the reply modal
            replyButton.addEventListener('click', () => {
                replyModal.style.display = 'flex';
            });

            // Close the reply modal
            closeReplyModal.addEventListener('click', () => {
                replyModal.style.display = 'none';
            });

            // Send reply logic
            sendReplyButton.addEventListener('click', () => {
                const messageText = replyText.value.trim();
                if (!messageText) {
                    alert('Reply cannot be empty.');A
                    return;
                }

                fetch('send_reply.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        original_message_id: <?php echo $messageId; ?>,
                        recipient_id: <?php echo $message['Sender_ID']; ?>,
                        message_text: messageText
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        replyModal.style.display = 'none';
                        window.location.reload();
                    } else {
                        alert(data.error || 'Failed to send reply.');
                    }
                })
                .catch(err => console.error('Error sending reply:', err));
            });
        });
    </script>
</body>
</html>
