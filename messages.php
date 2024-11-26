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

// Handle Actions (Delete, Restore, Mark as Read)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $messageId = intval($_POST['message_id'] ?? 0);

    if ($messageId && $action) {
        if ($action === 'delete') {
            // Move message to Trash
            $updateQuery = "UPDATE messages SET Deleted_Status = 1 WHERE Message_ID = ? AND (Sender_ID = ? OR Recipient_ID = ?)";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("iii", $messageId, $userId, $userId);
            $stmt->execute();
        } elseif ($action === 'restore') {
            // Restore message from Trash
            $updateQuery = "UPDATE messages SET Deleted_Status = 0 WHERE Message_ID = ? AND Recipient_ID = ?";
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
        .email-thumbnail img {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            margin-right: 10px;
            vertical-align: middle;
        }
        .email-thumbnail span {
            vertical-align: middle;
        }
        .email-table td, .email-table th {
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="email-layout">
        <div class="sidebar">
            <ul class="email-nav">
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
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="message_id" value="<?php echo $message['Message_ID']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit">Delete</button>
                                    </form>
                                    <button onclick="replyMessage(<?php echo $message['Message_ID']; ?>)">Reply</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Sent Section -->
            <div id="sent" class="email-section" style="display:none;">
                <h2>Sent</h2>
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
                                    <button onclick="replyMessage(<?php echo $message['Message_ID']; ?>)">Reply</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Trash Section -->
            <div id="trash" class="email-section" style="display:none;">
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
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="message_id" value="<?php echo $message['Message_ID']; ?>">
                                        <input type="hidden" name="action" value="restore">
                                        <button type="submit">Restore</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.email-section');
            sections.forEach(section => section.style.display = 'none');
            document.getElementById(sectionId).style.display = 'block';
        }

        function viewMessage(messageId) {
            alert("View message ID: " + messageId);
        }

        function replyMessage(messageId) {
            alert("Reply to message ID: " + messageId);
        }
    </script>
</body>
</html>
