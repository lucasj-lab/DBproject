<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Platform</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .message-view {
            display: none;
            padding: 20px;
            border-top: 1px solid #ccc;
            background: #f9f9f9;
            grid-column: 1 / -1;
        }
        .message-view h3 {
            margin-top: 0;
        }
        .message-view .message-content {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="email-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="email-nav">
                <li onclick="showSection('inbox')">Inbox</li>
                <li onclick="showSection('drafts')">Drafts</li>
                <li onclick="showSection('sent')">Sent</li>
                <li onclick="showSection('trash')">Trash</li>
                <li onclick="showSection('deleted')">Deleted</li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Shared Table Structure -->
            <template id="email-table-template">
                <table class="email-table">
                    <thead>
                        <tr>
                            <th>Thumbnail</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <!-- Inline Message View -->
                <div class="message-view" id="messageView">
                    <button onclick="closeMessageView()" style="float: right;">Close</button>
                    <h3 id="messageSubject">Message Subject</h3>
                    <p><strong>From:</strong> <span id="messageSender"></span></p>
                    <p><strong>Date:</strong> <span id="messageDate"></span></p>
                    <div class="message-content" id="messageContent"></div>
                </div>
            </template>

            <!-- Inbox Section -->
            <div id="inbox" class="email-section">
                <h2>Inbox</h2>
                <div id="inboxTable"></div>
            </div>

            <!-- Drafts Section -->
            <div id="drafts" class="email-section" style="display: none;">
                <h2>Drafts</h2>
                <div id="draftsTable"></div>
            </div>

            <!-- Sent Section -->
            <div id="sent" class="email-section" style="display: none;">
                <h2>Sent Mail</h2>
                <div id="sentTable"></div>
            </div>

            <!-- Trash Section -->
            <div id="trash" class="email-section" style="display: none;">
                <h2>Trash</h2>
                <div id="trashTable"></div>
            </div>

            <!-- Deleted Section -->
            <div id="deleted" class="email-section" style="display: none;">
                <h2>Deleted</h2>
                <div id="deletedTable"></div>
            </div>
        </div>
    </div>

    <script>
        // Data Placeholder (Should be populated with PHP in production)
        const emailSections = {
            inbox: <?php echo json_encode($inboxMessages); ?>,
            drafts: <?php echo json_encode($draftMessages); ?>,
            sent: <?php echo json_encode($sentMessages); ?>,
            trash: <?php echo json_encode($trashMessages); ?>,
            deleted: <?php echo json_encode($deletedMessages); ?>
        };

        // Populate sections
        Object.keys(emailSections).forEach(section => {
            const tableTemplate = document.getElementById('email-table-template').content.cloneNode(true);
            const tbody = tableTemplate.querySelector('tbody');
            const sectionData = emailSections[section] || [];
            sectionData.forEach(message => {
                const row = document.createElement('tr');
                row.onclick = () => openMessageView(message);
                row.innerHTML = `
                    <td>
                        <img src="${message.Thumbnail_URL || 'uploads/default-thumbnail.jpg'}" alt="Thumbnail">
                    </td>
                    <td>${message.Title || 'No Subject'}</td>
                    <td>${message.Message_Text.slice(0, 50)}...</td>
                    <td><button onclick="openMessageView(${JSON.stringify(message)})">View</button></td>
                `;
                tbody.appendChild(row);
            });
            document.getElementById(`${section}Table`).appendChild(tableTemplate);
        });

        // Show Section
        function showSection(sectionId) {
            document.querySelectorAll('.email-section').forEach(section => {
                section.style.display = 'none';
            });
            document.getElementById(sectionId).style.display = 'block';
        }

        // Open Message View
        function openMessageView(message) {
            document.getElementById('messageSubject').textContent = message.Title || 'No Subject';
            document.getElementById('messageSender').textContent = message.Sender_Name || 'Unknown Sender';
            document.getElementById('messageDate').textContent = new Date(message.Created_At).toLocaleString();
            document.getElementById('messageContent').textContent = message.Message_Text || 'No content';
            document.getElementById('messageView').style.display = 'block';
        }

        // Close Message View
        function closeMessageView() {
            document.getElementById('messageView').style.display = 'none';
        }

        // Default Section
        showSection('inbox');
    </script>
</body>
</html>
