// Send a message
function sendMessage(formId, notificationId) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    const notification = document.getElementById(notificationId);

    fetch('send_message.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notification.innerHTML = `<div class="alert success">${data.message}</div>`;
                form.reset(); // Clear the form
                fetchMessages(); // Refresh messages if applicable
            } else {
                notification.innerHTML = `<div class="alert error">${data.error}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            notification.innerHTML = `<div class="alert error">An unexpected error occurred. Please try again later.</div>`;
        });
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

            if (messages.length === 0) {
                container.innerHTML = `<p>No messages found.</p>`;
                return;
            }

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
        .catch(error => {
            console.error('Error fetching messages:', error);
            const container = document.getElementById('messagesContainer');
            container.innerHTML = `<p class="error">An error occurred while fetching messages. Please try again later.</p>`;
        });
}

// Apply filter for messages
function applyFilter() {
    const filter = document.getElementById('filter').value; // Get selected filter value
    const urlParams = new URLSearchParams(window.location.search);

    // Set the filter parameter
    urlParams.set('filter', filter);

    // Reload the page with the updated URL
    window.location.href = `${window.location.pathname}?${urlParams.toString()}`;
}
