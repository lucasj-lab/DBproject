document.addEventListener("DOMContentLoaded", function () {
    const replyModal = document.getElementById('replyModal');
    const closeReplyModal = document.getElementById('closeReplyModal');
    const replyButton = document.getElementById('replyButton');
    const sendReplyButton = document.getElementById('sendReply');
    const replyText = document.getElementById('replyText');

    // Open the modal
    replyButton.addEventListener('click', () => {
        replyModal.style.display = 'flex';
    });

    // Close the modal
    closeReplyModal.addEventListener('click', () => {
        replyModal.style.display = 'none';
    });

    // Send the reply
    sendReplyButton.addEventListener('click', () => {
        const messageText = replyText.value.trim();
        const messageId = replyButton.dataset.messageId;
        const senderId = replyButton.dataset.senderId;

        if (!messageText) {
            alert('Reply cannot be empty.');
            return;
        }

        fetch('send_reply.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                original_message_id: messageId,
                recipient_id: senderId,
                message_text: messageText
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Failed to send reply.');
            }
        })
        .catch(err => console.error('Fetch error:', err));
    });
});
