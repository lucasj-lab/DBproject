<?php
require 'database_connection.php';
include 'header.php';

// Ensure user is logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
} else {
    echo "<div class='error-message'>Error: User is not logged in.</div>";
    exit;
}

try {
    // Fetch Inbox Messages
    $inboxQuery = "
        SELECT m.Message_ID, m.Message_Text, m.Created_At, u.Name AS Sender_Name
        FROM messages m
        JOIN user u ON m.Sender_ID = u.User_ID
        WHERE m.Recipient_ID = :user_id AND m.Deleted_Status = 0
        ORDER BY m.Created_At DESC
    ";
    $inboxStmt = $pdo->prepare($inboxQuery);
    $inboxStmt->execute([':user_id' => $userId]);
    $inboxMessages = $inboxStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Sent Messages
    $sentQuery = "
        SELECT m.Message_ID, m.Message_Text, m.Created_At, u.Name AS Recipient_Name
        FROM messages m
        JOIN user u ON m.Recipient_ID = u.User_ID
        WHERE m.Sender_ID = :user_id AND m.Deleted_Status = 0
        ORDER BY m.Created_At DESC
    ";
    $sentStmt = $pdo->prepare($sentQuery);
    $sentStmt->execute([':user_id' => $userId]);
    $sentMessages = $sentStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Trash Messages
    $trashQuery = "
        SELECT m.Message_ID, m.Message_Text, m.Created_At, 
        IF(m.Sender_ID = :user_id, u.Name, 'You') AS Other_User
        FROM messages m
        JOIN user u ON (m.Sender_ID = u.User_ID OR m.Recipient_ID = u.User_ID)
        WHERE (m.Sender_ID = :user_id OR m.Recipient_ID = :user_id) AND m.Deleted_Status = 1
        ORDER BY m.Created_At DESC
    ";
    $trashStmt = $pdo->prepare($trashQuery);
    $trashStmt->execute([':user_id' => $userId]);
    $trashMessages = $trashStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='error-message'>Error fetching messages: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}

// Handle delete, restore, and delete forever actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_message_id'])) {
        $messageID = $_POST['delete_message_id'];
        $updateQuery = "
            UPDATE messages
            SET Deleted_Status = 1
            WHERE Message_ID = :message_id AND (Sender_ID = :user_id OR Recipient_ID = :user_id)
        ";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute([':message_id' => $messageID, ':user_id' => $userId]);
        header("Location: messages.php");
        exit;
    }

    if (isset($_POST['restore_message_id'])) {
        $messageID = $_POST['restore_message_id'];
        $updateQuery = "
            UPDATE messages
            SET Deleted_Status = 0
            WHERE Message_ID = :message_id AND Recipient_ID = :user_id
        ";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute([':message_id' => $messageID, ':user_id' => $userId]);
        header("Location: messages.php");
        exit;
    }

    if (isset($_POST['delete_forever_message_id'])) {
        $messageID = $_POST['delete_forever_message_id'];
        $deleteQuery = "
            DELETE FROM messages
            WHERE Message_ID = :message_id AND Recipient_ID = :user_id
        ";
        $stmt = $pdo->prepare($deleteQuery);
        $stmt->execute([':message_id' => $messageID, ':user_id' => $userId]);
        header("Location: messages.php");
        exit;
    }
}
?>

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
        
    function confirmDelete(form) {
        const confirmModal = document.getElementById('confirmDeleteModal');
        const deleteConfirmButton = document.getElementById('confirmDeleteButton');

        // Show the modal
        confirmModal.style.display = 'block';

        // Handle confirmation
        deleteConfirmButton.onclick = () => {
            confirmModal.style.display = 'none'; // Close modal
            form.submit(); // Submit the form
        };

        // Prevent form submission until confirmed
        return false;
    }

    // Close the modal when clicking outside it
    window.onclick = (event) => {
        const modal = document.getElementById('confirmDeleteModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };

    // Close the modal when clicking the "Close" button
    document.getElementById('closeModalButton').onclick = function () {
        document.getElementById('confirmDeleteModal').style.display = 'none';
    };

    </script>
</body>

</html>