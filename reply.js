document.addEventListener("DOMContentLoaded", function () {
    const replyButton = document.getElementById('replyButton');
    const replyModal = document.getElementById('replyModal');
    const closeReplyModal = document.getElementById('closeReplyModal');
    const sendReplyButton = document.getElementById('sendReply');
    const replyText = document.getElementById('replyText');

    replyButton.addEventListener('click', () => {
        replyModal.style.display = 'flex';
    });

    closeReplyModal.addEventListener('click', () => {
        replyModal.style.display = 'none';
    });

    sendReplyButton.addEventListener('click', () => {
        const messageId = replyButton.dataset.messageId;
        const senderId = replyButton.dataset.senderId;
        const messageText = replyText.value.trim();

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
                alert(data.message);
                replyModal.style.display = 'none';
                window.location.reload();
            } else {
                alert(data.error || 'Failed to send reply.');
            }
        })
        .catch(err => console.error("Fetch error:", err));
    });
});
