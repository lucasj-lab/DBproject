document.addEventListener("DOMContentLoaded", function () {
    const replyModal = document.getElementById('replyModal');
    const closeReplyModal = document.getElementById('closeReplyModal');
    const replyButton = document.getElementById('replyButton');
    const sendReplyButton = document.getElementById('sendReply');
    const replyText = document.getElementById('replyText');

    // Open modal
    replyButton.addEventListener('click', () => {
        console.log("Reply button clicked");
        replyModal.style.display = 'flex';
    });

    // Close modal
    closeReplyModal.addEventListener('click', () => {
        console.log("Close modal clicked");
        replyModal.style.display = 'none';
    });

    // Send reply
    sendReplyButton.addEventListener('click', () => {
        const messageText = replyText.value.trim();
        if (!messageText) {
            alert('Reply cannot be empty.');
            console.log("Empty reply text");
            return;
        }

        console.log("Sending reply:", messageText);

        fetch('send_reply.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                original_message_id: messageId, // From global variable or dataset
                recipient_id: senderId,        // From global variable or dataset
                message_text: messageText
            })
        })
        .then(response => {
            console.log("Fetch response received:", response);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log("Reply sent successfully:", data);
                alert(data.message);
                replyModal.style.display = 'none';
                window.location.reload();
            } else {
                console.error("Error from server:", data.error);
                alert(data.error || 'Failed to send reply.');
            }
        })
        .catch(err => console.error("Fetch error:", err));
    });
});
