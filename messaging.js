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

document.addEventListener('DOMContentLoaded', () => {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const messageCheckboxes = document.querySelectorAll('.messageCheckbox');

    // Toggle all checkboxes when "Select All" is clicked
    selectAllCheckbox.addEventListener('change', () => {
        const isChecked = selectAllCheckbox.checked;
        messageCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
        });
    });

    // Update "Select All" checkbox if a single checkbox is unchecked
    messageCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            if (!checkbox.checked) {
                selectAllCheckbox.checked = false;
            } else if (Array.from(messageCheckboxes).every(cb => cb.checked)) {
                selectAllCheckbox.checked = true;
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const messageRows = document.querySelectorAll(".message-row");
    const messageViewer = document.querySelector(".message-viewer .message-content");

    // Event listener for each row
    messageRows.forEach((row) => {
        row.addEventListener("click", () => {
            const messageId = row.getAttribute("data-id");

            // Highlight the selected row
            messageRows.forEach(r => r.classList.remove("active"));
            row.classList.add("active");

            // Fetch the message content
            fetch(`view_messages.php?message_id=${messageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageViewer.innerHTML = `
                            <strong>From:</strong> ${data.message.Sender_Name}<br>
                            <strong>Date:</strong> ${data.message.Created_At}<br><br>
                            ${data.message.Message_Text}
                        `;
                    } else {
                        messageViewer.textContent = "Error loading message.";
                    }
                })
                .catch(() => {
                    messageViewer.textContent = "Error fetching the message.";
                });
        });
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const messageRows = document.querySelectorAll(".message-row");
    const messageViewer = document.querySelector(".message-viewer .message-content");

    // Event listener for each row
    messageRows.forEach((row) => {
        row.addEventListener("click", () => {
            const messageId = row.getAttribute("data-id");

            // Highlight the selected row
            messageRows.forEach(r => r.classList.remove("active"));
            row.classList.add("active");

            // Fetch the message content
            fetch(`view_messages.php?message_id=${messageId}`, { headers: { "X-Requested-With": "XMLHttpRequest" } })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const { Message_Text, Created_At, Sender_Name, Replies } = data.message;

                        messageViewer.innerHTML = `
                            <strong>From:</strong> ${Sender_Name}<br>
                            <strong>Date:</strong> ${Created_At}<br><br>
                            <p>${Message_Text}</p>
                            <h3>Replies:</h3>
                            ${Replies.length > 0 
                                ? Replies.map(reply => `
                                    <div class="reply">
                                        <strong>${reply.Sender_Name}</strong> (${reply.Created_At}):
                                        <p>${reply.Reply_Text}</p>
                                    </div>
                                `).join("") 
                                : "<p>No replies yet.</p>"}
                        `;
                    } else {
                        messageViewer.textContent = "Error loading message.";
                    }
                })
                .catch(() => {
                    messageViewer.textContent = "Error fetching the message.";
                });
        });
    });
});
document.addEventListener("DOMContentLoaded", () => {
    const messageRows = document.querySelectorAll(".message-row");
    const messageViewer = document.querySelector(".message-viewer .message-content");

    messageRows.forEach((row) => {
        row.addEventListener("click", () => {
            const messageId = row.getAttribute("data-id");

            // Make an AJAX request to fetch the message details
            fetch(`view_message.php?message_id=${messageId}`)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error("Failed to fetch message.");
                    }
                    return response.text(); // Assume the PHP script returns raw HTML for the message
                })
                .then((data) => {
                    // Update the message viewer
                    messageViewer.innerHTML = data;
                })
                .catch((error) => {
                    console.error(error);
                    messageViewer.innerHTML = "<p>Error loading message. Please try again.</p>";
                });
        });
    });
});

// Hamburger Menu Toggle
document.querySelector(".hamburger-menu").addEventListener("click", function () {
    const navBar = document.querySelector('.messages-nav-bar');
    const formContainer = document.querySelector('.form-container');
    const isExpanded = navBar.classList.toggle('expanded'); // Toggle expanded class

    if (isExpanded) {
        navBar.classList.add('toggled'); // Lock in expanded state
    } else {
        navBar.classList.remove('toggled'); // Allow hover functionality
    }
});

// Hover effect to expand messages-nav bar temporarily
document.querySelector('.messages-nav-bar').addEventListener('mouseover', () => {
    const navBar = document.querySelector('.messages-nav-bar');
    if (!navBar.classList.contains('toggled')) {
        navBar.classList.add('expanded'); // Temporarily expand on hover
    }
});

// Collapse messages-nav bar when the mouse leaves
document.querySelector('.messages-nav-bar').addEventListener('mouseleave', () => {
    const messagesNavBar = document.querySelector('.messages-nav-bar');
    if (!navBar.classList.contains('toggled')) {
        navBar.classList.remove('expanded'); // Collapse if not permanently toggled
    }
});

const divider = document.querySelector('.messages-resizable-divider');
const leftPane = document.querySelector('.messages-messages-section');
const rightPane = document.querySelector('.messages-message-view-section');
const container = document.querySelector('.messages-resizable-container');

let isDragging = false;

// Start dragging
divider.addEventListener('mousedown', (e) => {
    isDragging = true;
    document.body.style.cursor = 'col-resize';
    e.preventDefault();
});

// Handle dragging
document.addEventListener('mousemove', (e) => {
    if (!isDragging) return;

    const containerRect = container.getBoundingClientRect();
    const leftWidth = e.clientX - containerRect.left;
    const rightWidth = containerRect.width - leftWidth;

    // Respect minimum sizes
    if (leftWidth >= 300 && rightWidth >= 300) {
        const leftFlex = leftWidth / containerRect.width;
        const rightFlex = rightWidth / containerRect.width;

        leftPane.style.flexGrow = leftFlex;
        rightPane.style.flexGrow = rightFlex;
    }
});

// Stop dragging
document.addEventListener('mouseup', () => {
    if (isDragging) {
        isDragging = false;
        document.body.style.cursor = '';
    }
});

// Selectors
const tbody = document.getElementById('messages-tbody');
const selectAllCheckbox = document.getElementById('select-all-messages');
const filterOptions = document.getElementById('filter-options');

// Sample Data Generation
const sampleMessages = Array.from({ length: 50 }, (_, i) => ({
    id: i + 1,
    from: `User ${i + 1}`,
    subject: `Subject ${i + 1}`,
    date: `2024-11-${30 - (i % 30)}`,
    read: i % 2 === 0, // Alternate read/unread
    starred: i % 3 === 0, // Alternate starred
}));

// Populate the table with sample data
function renderMessages(filter = 'all') {
    tbody.innerHTML = sampleMessages
        .filter((message) => {
            if (filter === 'all') return true;
            if (filter === 'none') return false;
            if (filter === 'read') return message.messages-read;
            if (filter === 'unread') return !message.messages-read;
            if (filter === 'starred') return message.messsages-starred;
            if (filter === 'unstarred') return !message.messages-starred;
        })
        .map(
            (message) => `
            <tr>
                <td><input type="checkbox" class="messages-message-select"></td>
                <td>
                    <span class="star-icon ${
                        message.starred ? 'active' : ''
                    }" data-id="${message.id}">⭐</span>
                </td>
                <td>${message.from}</td>
                <td>${message.subject}</td>
                <td>${message.date}</td>
            </tr>
        `
        )
        .join('');
}

// Toggle Star Status
tbody.addEventListener('click', (e) => {
    if (e.target.classList.contains('messages-star-icon')) {
        const id = e.target.dataset.id;
        const message = sampleMessages.find((msg) => msg.id === Number(id));
        message.starred = !message.starred;
        renderMessages(filterOptions.value);
    }
});

// Handle Select All Checkbox
selectAllCheckbox.addEventListener('change', (e) => {
    document
        .querySelectorAll('.messages-message-select')
        .forEach((checkbox) => (checkbox.checked = e.target.checked));
});

// Handle Filter Change
filterOptions.addEventListener('change', (e) => {
    renderMessages(e.target.value);
});

// Initial Render
renderMessages();

// Add event listeners to all message checkboxes
document.querySelectorAll('.messages-message-select').forEach((checkbox) => {
    checkbox.addEventListener('change', (e) => {
        const row = e.target.closest('tr'); // Get the parent row
        if (e.target.checked) {
            row.classList.add('selected');
        } else {
            row.classList.remove('selected');
        }
    });
});


// Add event listeners to tabs
document.querySelectorAll('.tab').forEach((tab) => {
    tab.addEventListener('click', (e) => {
        // Remove active class from all tabs
        document.querySelectorAll('.tab').forEach((t) => t.classList.remove('active'));

        // Add active class to the clicked tab
        e.target.classList.add('active');
    });
});



// Add event listeners to tabs for dynamic highlighting
document.querySelectorAll('.messages-header-table .tab').forEach((tab) => {
    tab.addEventListener('click', (e) => {
        // Remove active class from all tabs
        document.querySelectorAll('.messages-header-table .tab').forEach((t) => t.classList.remove('active'));

        // Add active class to the clicked tab
        e.target.classList.add('active');

        // Load the data based on the section clicked
        loadSectionData(e.target.id);
    });
});

// Load data for different sections
function loadSectionData(section) {
    const messagesTableBody = document.querySelector('.messages-table tbody');
    messagesTableBody.innerHTML = ''; // Clear previous data

    // Dummy data for each section
    const dummyData = {
        'primary-tab': [
            { from: 'John Doe', subject: 'Important Meeting', date: '2024-11-30', status: 'unread' },
            { from: 'Alice Brown', subject: 'Hello!', date: '2024-11-29', status: 'read' },
            { from: 'Jake Paul', subject: 'Follow-up Email', date: '2024-11-28', status: 'unread' },
        ],
        'promotions-tab': [
            { from: 'Shopify', subject: 'Holiday Deals', date: '2024-11-30', status: 'unread' },
            { from: 'Amazon', subject: 'Exclusive Offers', date: '2024-11-29', status: 'read' },
        ],
        'social-tab': [
            { from: 'Facebook', subject: 'New Friend Request', date: '2024-11-30', status: 'unread' },
            { from: 'LinkedIn', subject: 'Profile Views', date: '2024-11-29', status: 'read' },
        ],
        'updates-tab': [
            { from: 'GitHub', subject: 'New Pull Request', date: '2024-11-30', status: 'unread' },
            { from: 'Slack', subject: 'Workspace Updates', date: '2024-11-29', status: 'read' },
        ],
        'sent-tab': [
            { from: 'You', subject: 'Project Update', date: '2024-11-29', status: 'read' },
            { from: 'You', subject: 'Follow-up Email', date: '2024-11-28', status: 'read' },
        ],
    };

    // Populate the table with data for the selected section
    const sectionData = dummyData[section] || [];
    sectionData.forEach((message) => {
        const row = document.createElement('tr');
        row.className = message.status;

        row.innerHTML = `
            <td><input type="checkbox" class="messages-message-select"></td>
            <td>
                <input type="checkbox" id="star-${Math.random()}" class="messages-star-checkbox">
                <label for="star-${Math.random()}" class="messages-star-label">⭐</label>
            </td>
            <td>${message.from}</td>
            <td>${message.subject}</td>
            <td>${message.date}</td>
        `;

        messagesTableBody.appendChild(row);
    });
}

// Load the default section (Primary) on page load
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('messages-primary-tab').classList.add('active');
    loadSectionData('messages-primary-tab');
});


// Hamburger Menu Toggle
document.querySelector('.messages-hamburger-menu').addEventListener('click', function () {
    const messagesNavBar = document.querySelector('.messages-nav-bar');
    const messagesFormContainer = document.querySelector('.messages-form-container');
    const isExpanded = navBar.classList.toggle('expanded'); // Toggle expanded class

    if (isExpanded) {
        messages.navBar.classList.add('toggled'); // Lock in expanded state
        messages.formContainer.style.marginLeft = '240px'; // Adjust form container margin
    } else {
        navBar.classList.remove('toggled'); // Allow hover functionality
        messages.formContainer.style.marginLeft = '60px'; // Reset form container margin
    }
});

// Hover effect to expand messages-nav bar temporarily
document.querySelector('.messages-messages-nav-bar').addEventListener('mouseover', () => {
    const messagesNavBar = document.querySelector('.messages-nav-bar');
    const messagesFormContainer = document.querySelector('.messages-form-container');
    if (!navBar.classList.contains('toggled')) {
        messagesNavBar.classList.add('expanded'); // Temporarily expand on hover
        messagesFormContainer.style.marginLeft = '240px'; // Temporarily adjust margin
    }
});

// Collapse messages-nav bar when the mouse leaves
document.querySelector('.messages-nav-bar').addEventListener('mouseleave', () => {
    const messagesNavBar = document.querySelector('.messages-nav-bar');
    const messagesFormContainer = document.querySelector('.messages-form-container');
    if (!navBar.classList.contains('toggled')) {
        messagesNavBar.classList.remove('expanded'); // Collapse if not permanently toggled
        messagesFormContainer.style.marginLeft = '60px'; // Reset margin
    }
});


