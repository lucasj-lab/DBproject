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

// View a specific message
function viewMessage(messageId) {
    // Redirect to the view_message.php page with the message ID
    window.location.href = `view_message.php?message_id=${messageId}`;
}

// Open a warning modal for actions like restore or delete
function openWarningModal(messageId, actionType) {
    const confirmation = confirm(
        actionType === "restore"
            ? "Are you sure you want to restore this message?"
            : "Are you sure you want to permanently delete this message?"
    );

    if (confirmation) {
        // Send a request to the server to perform the action
        fetch("mark_notification_read.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `message_id=${messageId}&action=${actionType}`
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    alert(data.message);
                    location.reload(); // Refresh the page to update the UI
                } else {
                    alert(data.error);
                }
            })
            .catch((error) => {
                console.error("Error:", error);
                alert("An error occurred. Please try again later.");
            });
    }
}

// Select all messages
function selectAllMessages() {
    document.querySelectorAll('input[name="selected_messages[]"]').forEach(checkbox => {
        checkbox.checked = true;
    });
}

// Unselect all messages
function unselectAllMessages() {
    document.querySelectorAll('input[name="selected_messages[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}

// Confirm deletion of selected messages
function confirmDelete() {
    return confirm("Are you sure you want to delete the selected messages?");
}

document.querySelectorAll('.send-reply-btn').forEach(button => {
    button.addEventListener('click', () => {
        const form = button.closest('form');
        const formData = new FormData(form);

        fetch('send_reply.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Reply sent successfully!');
                    form.reset();
                } else {
                    alert('Failed to send reply.');
                }
            })
            .catch(error => console.error('Error:', error));
    });
});


document.addEventListener("DOMContentLoaded", () => {
    // Sidebar toggle
    const toggleSidebar = document.getElementById("toggleSidebar");
    const sidebar = document.querySelector(".sidebar");
    toggleSidebar.addEventListener("click", () => {
        sidebar.classList.toggle("collapsed");
    });

    // Select/Deselect All Checkboxes
    const masterCheckbox = document.querySelector("thead .checkbox-container input");
    const messageCheckboxes = document.querySelectorAll("tbody .checkbox-container input");

    if (masterCheckbox) {
        masterCheckbox.addEventListener("change", (e) => {
            const isChecked = e.target.checked;
            messageCheckboxes.forEach((checkbox) => {
                checkbox.checked = isChecked;
            });
        });
    }

    // Individual message actions (archive, delete, mark unread)
    const actionButtons = document.querySelectorAll(".actions-cell button");
    actionButtons.forEach((button) => {
        button.addEventListener("click", (e) => {
            const action = button.title.toLowerCase(); // e.g., 'archive', 'delete'
            const messageRow = button.closest("tr");
            const messageId = messageRow.querySelector(".checkbox-container input").value;

            // Perform action via AJAX (if backend integration is needed)
            performAction(action, messageId).then((response) => {
                if (response.success) {
                    if (action === "delete") {
                        messageRow.remove(); // Remove row on successful delete
                    } else {
                        messageRow.classList.toggle("unread", action === "mark as unread");
                        messageRow.classList.toggle("read", action === "mark as read");
                    }
                } else {
                    console.error("Action failed:", response.message);
                }
            });
        });
    });

    // Mark messages as important
    const importanceIcons = document.querySelectorAll(".importance-cell div");
    importanceIcons.forEach((icon) => {
        icon.addEventListener("click", () => {
            const isImportant = icon.getAttribute("aria-checked") === "true";
            icon.setAttribute("aria-checked", !isImportant);
            icon.title = isImportant ? "Mark as important" : "Unmark as important";
        });
    });
});

// Function to simulate AJAX calls to the backend (replace with actual AJAX logic)
function performAction(action, messageId) {
    return new Promise((resolve) => {
        console.log(`Performing action: ${action} on message ID: ${messageId}`);
        setTimeout(() => {
            resolve({ success: true }); // Simulate successful response
        }, 500);
    });
}
