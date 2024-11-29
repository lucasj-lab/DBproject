document.addEventListener("DOMContentLoaded", function () {
    const replyModal = document.getElementById('replyModal');
    const closeReplyModal = document.getElementById('closeReplyModal');
    const replyButton = document.getElementById('replyButton');
    const sendReplyButton = document.getElementById('sendReply');
    const replyText = document.getElementById('replyText');

    // Open modal
    replyButton.addEventListener('click', () => {
        replyModal.style.display = 'flex';
    });

    // Close modal
    closeReplyModal.addEventListener('click', () => {
        replyModal.style.display = 'none';
    });

    // Send reply
    sendReplyButton.addEventListener('click', () => {
        const messageText = replyText.value.trim();
        if (!messageText) {
            alert('Reply cannot be empty.');
            return;
        }

        fetch('send_reply.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                original_message_id: <?php echo json_encode($messageId); ?>,
                recipient_id: <?php echo json_encode($message['Sender_ID']); ?>,
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
