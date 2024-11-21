// Send a message
function sendMessage(formId) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);

    fetch('send_message.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                form.reset(); // Clear the form
                fetchMessages(); // Refresh messages
            } else {
                alert(data.error);
            }
        })
        .catch(error => console.error('Error:', error));
}

// Fetch messages
function fetchMessages(senderId, receiverId, listingId = null) {
    const params = new URLSearchParams({ sender_id: senderId, receiver_id: receiverId });
    if (listingId) params.append('listing_id', listingId);

    fetch(`fetch_messages.php?${params.toString()}`)
        .then(response => response.json())
        .then(messages => {
            const container = document.getElementById('messagesContainer');
            container.innerHTML = ''; // Clear previous messages

            messages.forEach(message => {
                const messageElement = document.createElement('div');
                messageElement.className = 'message-item';
                messageElement.innerHTML = `
                    <p><strong>${message.Sender_Username}:</strong> ${message.Message_Text}</p>
                    <small>${new Date(message.Date_Sent).toLocaleString()}</small>
                `;
                container.appendChild(messageElement);
            });
        })
        .catch(error => console.error('Error fetching messages:', error));
}
