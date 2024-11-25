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
            <!-- Inbox Section -->
            <div id="inbox" class="email-section">
                <h2>Inbox</h2>
                <table class="email-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inboxMessages as $message): ?>
                            <tr onclick="viewMessage('<?php echo htmlspecialchars($message['Message_ID']); ?>')">
                                <td>
                                    <div class="email-thumbnail">
                                        <img src="<?php echo htmlspecialchars($message['Thumbnail_URL']); ?>" alt="Listing Thumbnail">
                                        <span><?php echo htmlspecialchars($message['Title']); ?></span>
                                    </div>
                                </td>
                                <td class="email-preview">
                                    <?php echo htmlspecialchars(substr($message['Message_Text'], 0, 50)); ?>...
                                </td>
                                <td class="email-view">
                                    <button onclick="viewMessage('<?php echo htmlspecialchars($message['Message_ID']); ?>')">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Drafts Section -->
            <div id="drafts" class="email-section" style="display: none;">
                <h2>Drafts</h2>
                <table class="email-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($draftMessages as $message): ?>
                            <tr onclick="viewMessage('<?php echo htmlspecialchars($message['Message_ID']); ?>')">
                                <td>
                                    <div class="email-thumbnail">
                                        <img src="<?php echo htmlspecialchars($message['Thumbnail_URL']); ?>" alt="Draft Thumbnail">
                                        <span><?php echo htmlspecialchars($message['Title']); ?></span>
                                    </div>
                                </td>
                                <td class="email-preview">
                                    <?php echo htmlspecialchars(substr($message['Message_Text'], 0, 50)); ?>...
                                </td>
                                <td class="email-view">
                                    <button onclick="viewMessage('<?php echo htmlspecialchars($message['Message_ID']); ?>')">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Sent Section -->
            <div id="sent" class="email-section" style="display: none;">
                <h2>Sent Mail</h2>
                <table class="email-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sentMessages as $message): ?>
                            <tr onclick="viewMessage('<?php echo htmlspecialchars($message['Message_ID']); ?>')">
                                <td>
                                    <div class="email-thumbnail">
                                        <img src="<?php echo htmlspecialchars($message['Thumbnail_URL']); ?>" alt="Sent Thumbnail">
                                        <span><?php echo htmlspecialchars($message['Title']); ?></span>
                                    </div>
                                </td>
                                <td class="email-preview">
                                    <?php echo htmlspecialchars(substr($message['Message_Text'], 0, 50)); ?>...
                                </td>
                                <td class="email-view">
                                    <button onclick="viewMessage('<?php echo htmlspecialchars($message['Message_ID']); ?>')">View</button>
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
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trashMessages as $message): ?>
                            <tr onclick="viewMessage('<?php echo htmlspecialchars($message['Message_ID']); ?>')">
                                <td>
                                    <div class="email-thumbnail">
                                        <img src="<?php echo htmlspecialchars($message['Thumbnail_URL']); ?>" alt="Trash Thumbnail">
                                        <span><?php echo htmlspecialchars($message['Title']); ?></span>
                                    </div>
                                </td>
                                <td class="email-preview">
                                    <?php echo htmlspecialchars(substr($message['Message_Text'], 0, 50)); ?>...
                                </td>
                                <td class="email-view">
                                    <button onclick="viewMessage('<?php echo htmlspecialchars($message['Message_ID']); ?>')">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Deleted Section -->
            <div id="deleted" class="email-section" style="display: none;">
                <h2>Deleted</h2>
                <table class="email-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deletedMessages as $message): ?>
                            <tr onclick="viewMessage('<?php echo htmlspecialchars($message['Message_ID']); ?>')">
                                <td>
                                    <div class="email-thumbnail">
                                        <img src="<?php echo htmlspecialchars($message['Thumbnail_URL']); ?>" alt="Deleted Thumbnail">
                                        <span><?php echo htmlspecialchars($message['Title']); ?></span>
                                    </div>
                                </td>
                                <td class="email-preview">
                                    <?php echo htmlspecialchars(substr($message['Message_Text'], 0, 50)); ?>...
                                </td>
                                <td class="email-view">
                                    <button onclick="viewMessage('<?php echo htmlspecialchars($message['Message_ID']); ?>')">View</button>
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

        function openMessageView(message) {
            // Populate the message view with selected message details
            document.getElementById('messageSubject').textContent = message.Listing_Title || 'No Subject';
            document.getElementById('messageSender').textContent = message.Sender_Name || 'Unknown Sender';
            document.getElementById('messageDate').textContent = new Date(message.Created_At).toLocaleString();
            document.getElementById('messageContent').textContent = message.Message_Text || 'No content';

            // Show the message view
            document.getElementById('messageView').style.display = 'block';
        }

        function closeMessageView() {
            // Hide the message view
            document.getElementById('messageView').style.display = 'none';
        }
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