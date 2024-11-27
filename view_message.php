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
    <div class="message-container-holder">
        <div class="message-container">
            <h2>Message Details</h2>
            <p><strong>From:</strong> <?php echo htmlspecialchars($message['Sender_Name']); ?></p>
            <p><strong>Subject:</strong> <?php echo htmlspecialchars($message['Subject'] ?: 'No Subject'); ?></p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($message['Created_At']); ?></p>
            <p><strong>Message:</strong></p>
            <p><?php echo nl2br(htmlspecialchars($message['Message_Text'])); ?></p>
            <a href="messages.php" class="btn">Back to Messages</a>
            <button id="replyButton" class="btn">Reply</button>
        </div>
    </div>

    <!-- Reply Modal -->
    <div id="replyModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="closeReplyModal">&times;</span>
            <h3>Reply to Message</h3>
            <textarea id="replyText" class="reply-textarea" placeholder="Type your reply here..."></textarea>
            <div class="modal-actions">
                <button id="saveDraft" class="btn draft-btn">Save Draft</button>
                <button id="sendReply" class="btn send-btn">Send</button>
            </div>
        </div>
    </div>

    <script>
        const replyModal = document.getElementById('replyModal');
        const closeReplyModal = document.getElementById('closeReplyModal');
        const replyButton = document.getElementById('replyButton');
        const saveDraftButton = document.getElementById('saveDraft');
        const sendReplyButton = document.getElementById('sendReply');
        const replyText = document.getElementById('replyText');

        replyButton.addEventListener('click', () => {
            replyModal.style.display = 'flex';
        });

        closeReplyModal.addEventListener('click', () => {
            saveDraft(); // Save draft when closing the modal
            replyModal.style.display = 'none';
        });

        saveDraftButton.addEventListener('click', saveDraft);

        sendReplyButton.addEventListener('click', () => {
            const messageText = replyText.value.trim();
            if (messageText) {
                fetch('send_reply.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        original_message_id: <?php echo $messageId; ?>,
                        message_text: messageText,
                        recipient_id: <?php echo $message['Sender_ID']; ?>
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Reply sent successfully!');
                        replyModal.style.display = 'none';
                        replyText.value = ''; // Clear the text box
                    } else {
                        alert(data.error || 'Failed to send reply.');
                    }
                })
                .catch(err => console.error('Error:', err));
            } else {
                alert('Message text cannot be empty.');
            }
        });

        function saveDraft() {
            const draftText = replyText.value.trim();
            if (draftText) {
                fetch('save_draft.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        message_text: draftText,
                        original_message_id: <?php echo $messageId; ?>
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Draft saved successfully!');
                    } else {
                        console.error(data.error);
                    }
                })
                .catch(err => console.error('Error saving draft:', err));
            }
        }
    </script>
</body>
</html>
