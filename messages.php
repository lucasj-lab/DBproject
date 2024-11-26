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
           u.Name AS Sender_Name, l.Title AS Listing_Title
    FROM messages m
    LEFT JOIN user u ON m.Sender_ID = u.User_ID
    LEFT JOIN listings l ON m.Listing_ID = l.Listing_ID
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
           u.Name AS Recipient_Name, l.Title AS Listing_Title
    FROM messages m
    LEFT JOIN user u ON m.Recipient_ID = u.User_ID
    LEFT JOIN listings l ON m.Listing_ID = l.Listing_ID
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
           u.Name AS Sender_Name, l.Title AS Listing_Title
    FROM messages m
    LEFT JOIN user u ON m.Sender_ID = u.User_ID
    LEFT JOIN listings l ON m.Listing_ID = l.Listing_ID
    WHERE m.Recipient_ID = ? AND m.Deleted_Status = 1
    ORDER BY m.Created_At DESC
";
$trashStmt = $conn->prepare($trashQuery);
$trashStmt->bind_param("i", $userId);
$trashStmt->execute();
$trashMessages = $trashStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$trashStmt->close();

// Handle Actions (Delete, Restore, Mark as Read)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $messageId = intval($_POST['message_id'] ?? 0);

    if ($messageId && $action) {
        if ($action === 'delete') {
            // Move message to Trash
            $updateQuery = "UPDATE messages SET Deleted_Status = 1 WHERE Message_ID = ? AND Recipient_ID = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ii", $messageId, $userId);
            $stmt->execute();
        } elseif ($action === 'restore') {
            // Restore message from Trash
            $updateQuery = "UPDATE messages SET Deleted_Status = 0 WHERE Message_ID = ? AND Recipient_ID = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ii", $messageId, $userId);
            $stmt->execute();
        } elseif ($action === 'mark_as_read') {
            // Mark message as read
            $updateQuery = "UPDATE messages SET Read_Status = 1 WHERE Message_ID = ? AND Recipient_ID = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ii", $messageId, $userId);
            $stmt->execute();
        }
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
    <title>Messages</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .message-container {
            margin: 20px 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }

        .actions button {
            margin: 0 5px;
        }

        .message-view {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="messages-container">
        <h1>Inbox</h1>
        <div>
            <?php foreach ($inboxMessages as $message): ?>
                <div class="message-container">
                    <p><strong>From:</strong> <?= htmlspecialchars($message['Sender_Name']) ?></p>
                    <p><strong>Subject:</strong> <?= htmlspecialchars($message['Subject']) ?></p>
                    <p><strong>Listing:</strong> <?= htmlspecialchars($message['Listing_Title'] ?? 'N/A') ?></p>
                    <p><strong>Received:</strong> <?= htmlspecialchars($message['Created_At']) ?></p>
                    <div class="actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="message_id" value="<?= $message['Message_ID'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit">Delete</button>
                        </form>
                        <button onclick="viewMessage(<?= $message['Message_ID'] ?>)">View</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <h1>Sent Mail</h1>
        <div>
            <?php foreach ($sentMessages as $message): ?>
                <div class="message-container">
                    <p><strong>To:</strong> <?= htmlspecialchars($message['Recipient_Name']) ?></p>
                    <p><strong>Subject:</strong> <?= htmlspecialchars($message['Subject']) ?></p>
                    <p><strong>Listing:</strong> <?= htmlspecialchars($message['Listing_Title'] ?? 'N/A') ?></p>
                    <p><strong>Sent:</strong> <?= htmlspecialchars($message['Created_At']) ?></p>
                    <div class="actions">
                        <button onclick="viewMessage(<?= $message['Message_ID'] ?>)">View</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <h1>Trash</h1>
        <div>
            <?php foreach ($trashMessages as $message): ?>
                <div class="message-container">
                    <p><strong>From:</strong> <?= htmlspecialchars($message['Sender_Name']) ?></p>
                    <p><strong>Subject:</strong> <?= htmlspecialchars($message['Subject']) ?></p>
                    <p><strong>Listing:</strong> <?= htmlspecialchars($message['Listing_Title'] ?? 'N/A') ?></p>
                    <p><strong>Deleted:</strong> <?= htmlspecialchars($message['Created_At']) ?></p>
                    <div class="actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="message_id" value="<?= $message['Message_ID'] ?>">
                            <input type="hidden" name="action" value="restore">
                            <button type="submit">Restore</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Modal for Viewing a Message -->
        <div id="messageModal" style="display: none; position: fixed; top: 10%; left: 10%; width: 80%; background: white; border: 1px solid #ddd; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); padding: 20px; z-index: 1000;">
            <div id="messageContent" class="message-view"></div>
            <button onclick="closeModal()">Close</button>
        </div>

        <script>
            function viewMessage(messageId) {
                fetch(`view_message.php?message_id=${messageId}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('messageContent').innerHTML = html;
                        document.getElementById('messageModal').style.display = 'block';
                    });
            }

            function closeModal() {
                document.getElementById('messageModal').style.display = 'none';
            }
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.email-section');
            sections.forEach(section => section.style.display = 'none');
            document.getElementById(sectionId).style.display = 'block';
        }
    </script>
</body>
</html>
