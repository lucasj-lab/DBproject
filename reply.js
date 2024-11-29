document.addEventListener("DOMContentLoaded", function () {
    const replyModal = document.getElementById('replyModal');
    const closeReplyModal = document.getElementById('closeReplyModal');
    const replyButton = document.getElementById('replyButton');
    const sendReplyButton = document.getElementById('sendReply');
    const replyText = document.getElementById('replyText');
    const successIndicator = document.getElementById('successIndicator');
    const repliesContainer = document.querySelector('.replies-container');

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
                original_message_id: replyButton.dataset.messageId,
                recipient_id: replyButton.dataset.senderId,
                message_text: messageText
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success indicator
                successIndicator.style.display = 'block';
                replyModal.style.display = 'none';

                // Add the new reply to the conversation
                const newReply = `
                    <div class="reply">
                        <p><strong>You:</strong> ${messageText.replace(/\n/g, '<br>')}</p>
                        <p><em>Just now</em></p>
                    </div>
                `;
                repliesContainer.innerHTML += newReply;

                // Clear the reply text field
                replyText.value = '';
            } else {
                alert(data.error || 'Failed to send reply.');
            }
        })
        .catch(err => console.error("Fetch error:", err));
    });
});
