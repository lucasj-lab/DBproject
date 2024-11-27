<?php
require 'database_connection.php';
include 'header.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view your messages.");
}
$userId = intval($_SESSION['user_id']);

// Fetch Inbox Messages
$inboxQuery = "
    SELECT m.Message_ID, m.Subject, m.Message_Text, m.Created_At, m.Read_Status, 
           u.Name AS Sender_Name, l.Title AS Listing_Title, i.Image_URL
    FROM messages m
    LEFT JOIN user u ON m.Sender_ID = u.User_ID
    LEFT JOIN listings l ON m.Listing_ID = l.Listing_ID
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE m.Recipient_ID = ? AND m.Deleted_Status = 0
    ORDER BY m.Created_At DESC
";
$inboxStmt = $conn->prepare($inboxQuery);
$inboxStmt->bind_param("i", $userId);
$inboxStmt->execute();
$inboxMessages = $inboxStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$inboxStmt->close();

// Fetch Sent Messages
$sentQuery = "
    SELECT m.Message_ID, m.Subject, m.Message_Text, m.Created_At, 
           u.Name AS Recipient_Name, l.Title AS Listing_Title, i.Image_URL
    FROM messages m
    LEFT JOIN user u ON m.Recipient_ID = u.User_ID
    LEFT JOIN listings l ON m.Listing_ID = l.Listing_ID
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE m.Sender_ID = ? AND m.Deleted_Status = 0
    ORDER BY m.Created_At DESC
";
$sentStmt = $conn->prepare($sentQuery);
$sentStmt->bind_param("i", $userId);
$sentStmt->execute();
$sentMessages = $sentStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$sentStmt->close();

// Fetch Trash Messages
$trashQuery = "
    SELECT m.Message_ID, m.Subject, m.Message_Text, m.Created_At, 
           u.Name AS Sender_Name, l.Title AS Listing_Title, i.Image_URL
    FROM messages m
    LEFT JOIN user u ON m.Sender_ID = u.User_ID
    LEFT JOIN listings l ON m.Listing_ID = l.Listing_ID
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE m.Recipient_ID = ? AND m.Deleted_Status = 1
    ORDER BY m.Created_At DESC
";
$trashStmt = $conn->prepare($trashQuery);
$trashStmt->bind_param("i", $userId);
$trashStmt->execute();
$trashMessages = $trashStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$trashStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            display: flex;
            margin: 0;
            font-family: Arial, sans-serif;
            flex-wrap: wrap;
        }

        .sidebar {
            position: sticky;
            top: 0;
            width: 100px;
            height: 100vh;
            background-color: #062247;
            color: #fff;
            padding: 20px 10px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            margin: 10px 0;
            cursor: pointer;
            text-align: center;
        }

        .sidebar ul li:hover {
            background-color: #444;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            overflow: auto;
        }

        .email-table {
            width: 100%;
            border-collapse: collapse;
        }

        .email-table th,
        .email-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .email-table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .modal {
            display: none;
            justify-content: center;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .modal-actions button {
            margin: 5px;
        }

        .email-thumbnail img {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            margin-right: 10px;
        }

        .email-thumbnail span {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <ul>
            <li onclick="showSection('inbox')">Inbox</li>
            <li onclick="showSection('sent')">Sent</li>
            <li onclick="showSection('trash')">Trash</li>
        </ul>
    </div>


    <div class="main-content">
        <!-- Inbox Section -->
        <div id="inbox" class="email-section">
            <h2>Inbox</h2>
            <table class="email-table">
                <thead>
                    <tr>
                        <th>Listing</th>
                        <th>Message</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inboxMessages as $message): ?>
                        <tr>
                            <td>
                                <div class="email-thumbnail">
                                    <?php if (!empty($message['Image_URL'])): ?>
                                        <img src="<?php echo htmlspecialchars($message['Image_URL']); ?>" alt="Thumbnail">
                                    <?php else: ?>
                                        <span>No Thumbnail</span>
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($message['Listing_Title'] ?? 'No Title'); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars(substr($message['Message_Text'], 0, 50)); ?>...</td>
                            <td>
                                <button onclick="viewMessage(<?php echo $message['Message_ID']; ?>)">View</button>
                                <button onclick="openWarningModal(<?php echo $message['Message_ID']; ?>, 'delete')">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Sent Section -->
        <div id="sent" class="email-section" style="display: none;">
            <h2>Sent Messages</h2>
            <table class="email-table">
                <thead>
                    <tr>
                        <th>Listing</th>
                        <th>Message</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sentMessages as $message): ?>
                        <tr>
                            <td>
                                <div class="email-thumbnail">
                                    <?php if (!empty($message['Image_URL'])): ?>
                                        <img src="<?php echo htmlspecialchars($message['Image_URL']); ?>" alt="Thumbnail">
                                    <?php else: ?>
                                        <span>No Thumbnail</span>
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($message['Listing_Title'] ?? 'No Title'); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars(substr($message['Message_Text'], 0, 50)); ?>...</td>
                            <td>
                                <button onclick="viewMessage(<?php echo $message['Message_ID']; ?>)">View</button>
                                <button onclick="openWarningModal(<?php echo $message['Message_ID']; ?>, 'delete')">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Trash Section -->
        <div id="trash" class="email-section" style="display: none;">
            <h2>Trash</h2>
            <table class="email-table">
                <thead>
                    <tr>
                        <th>Listing</th>
                        <th>Message</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trashMessages as $message): ?>
                        <tr>
                            <td>
                                <div class="email-thumbnail">
                                    <?php if (!empty($message['Image_URL'])): ?>
                                        <img src="<?php echo htmlspecialchars($message['Image_URL']); ?>" alt="Thumbnail">
                                    <?php else: ?>
                                        <span>No Thumbnail</span>
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($message['Listing_Title'] ?? 'No Title'); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars(substr($message['Message_Text'], 0, 50)); ?>...</td>
                            <td>
                                <button onclick="viewMessage(<?php echo $message['Message_ID']; ?>)">View</button>
                                <button onclick="openWarningModal(<?php echo $message['Message_ID']; ?>, 'restore')">Restore</button>
                                <button onclick="openWarningModal(<?php echo $message['Message_ID']; ?>, 'delete_forever')">Delete Forever</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
  <!-- Warning Modal -->
  <div id="warningModal" class="modal">
        <div class="modal-content">
            <h2 id="warningTitle">Warning</h2>
            <p id="warningMessage">Are you sure you want to perform this action?</p>
            <div class="modal-actions">
                <button id="confirmActionBtn" class="btn btn-danger">Yes</button>
                <button onclick="closeWarningModal()" class="btn">Cancel</button>
            </div>
        </div>
    </div>
    
    <script>
        function showSection(sectionId) {
            // Hide all sections
            const sections = document.querySelectorAll('.email-section');
            sections.forEach(section => {
                section.style.display = 'none';
            });

            // Show the selected section
            const selectedSection = document.getElementById(sectionId);
            if (selectedSection) {
                selectedSection.style.display = 'block';
            }
        }

        let actionForm = null;

        function showSection(sectionId) {
            const sections = document.querySelectorAll('.email-section');
            sections.forEach(section => section.style.display = 'none');
            document.getElementById(sectionId).style.display = 'block';
        }

        function openWarningModal(messageId, actionType) {
            const modal = document.getElementById('warningModal');
            const warningMessage = document.getElementById('warningMessage');

            if (actionType === 'delete') {
                warningMessage.textContent = "Are you sure you want to move this message to Trash?";
            } else if (actionType === 'restore') {
                warningMessage.textContent = "Are you sure you want to restore this message?";
            }

            document.getElementById('confirmActionBtn').onclick = () => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = actionType;

                const messageIdInput = document.createElement('input');
                messageIdInput.type = 'hidden';
                messageIdInput.name = 'message_id';
                messageIdInput.value = messageId;

                form.appendChild(actionInput);
                form.appendChild(messageIdInput);
                document.body.appendChild(form);

                form.submit();
            };

            modal.style.display = 'flex';
        }

        function closeWarningModal() {
            const modal = document.getElementById('warningModal');
            modal.style.display = 'none';
        }
    </script>
</body>
</html>
