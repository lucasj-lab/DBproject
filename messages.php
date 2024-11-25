<?php
require 'database_connection.php';
include 'header.php';

// Ensure user is logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id']; // Correctly defined variable
} else {
    echo "<div class='error-message'>Error: User is not logged in.</div>";
    exit; // Stop further execution if the user is not logged in
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
        IF(m.Sender_ID = :user_id, u.Name, 'You') AS Other_User,
        m.Deleted_Status
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

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message_id'])) {
    $messageID = $_POST['delete_message_id'];

    try {
        $updateQuery = "
            UPDATE messages
            SET Deleted_Status = 1
            WHERE Message_ID = :message_id AND (Sender_ID = :user_id OR Recipient_ID = :user_id)
        ";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute([
            ':message_id' => $messageID,
            ':user_id' => $userId
        ]);

        // Redirect to prevent re-submission
        header("Location: messages.php");
        exit;
    } catch (PDOException $e) {
        echo "<div class='error-message'>Error deleting message: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

//restore/deleteforever 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['restore_message_id'])) {
        $messageID = $_POST['restore_message_id'];

        try {
            $updateQuery = "
                UPDATE messages
                SET Deleted_Status = 0
                WHERE Message_ID = :message_id AND Recipient_ID = :user_id
            ";
            $stmt = $pdo->prepare($updateQuery);
            $stmt->execute([':message_id' => $messageID, ':user_id' => $userId]);

            $success_message = 'Message restored successfully!';
        } catch (PDOException $e) {
            $error_message = 'Error restoring message: ' . htmlspecialchars($e->getMessage());
        }
    }

    if (isset($_POST['delete_forever_message_id'])) {
        $messageID = $_POST['delete_forever_message_id'];

        try {
            $deleteQuery = "
                DELETE FROM messages
                WHERE Message_ID = :message_id AND Recipient_ID = :user_id
            ";
            $stmt = $pdo->prepare($deleteQuery);
            $stmt->execute([':message_id' => $messageID, ':user_id' => $userId]);

            $success_message = 'Message permanently deleted!';
        } catch (PDOException $e) {
            $error_message = 'Error deleting message permanently: ' . htmlspecialchars($e->getMessage());
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="styles.css"> <!-- Ensure you have styles.css linked -->
</head>

<body>
    
<div class="feedback">
    <?php if (!empty($success_message)): ?>
        <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>
</div>

    <div class="messages-container">
        <h1 class="messages-title">Messages</h1>

        <!-- Navigation Tabs -->
        <ul class="tabs">
            <li><a href="#inbox">Inbox</a></li>
            <li><a href="#sent">Sent Mail</a></li>
            <li><a href="#trash">Trash</a></li>
        </ul>

        <!-- Inbox Section -->
        <div id="inbox" class="messages-section">
            <h2 class="section-title">Inbox</h2>
            <ul class="messages-list">
                <?php if (!empty($inboxMessages)): ?>
                    <?php foreach ($inboxMessages as $message): ?>
                        <li class="message-item">
                            <p><strong>From:</strong> <?php echo htmlspecialchars($message['Sender_Name']); ?></p>
                            <p><?php echo htmlspecialchars($message['Message_Text']); ?></p>
                            <small><?php echo htmlspecialchars($message['Created_At']); ?></small>
                            <form method="POST" class="delete-form">
                                <input type="hidden" name="delete_message_id"
                                    value="<?php echo htmlspecialchars($message['Message_ID']); ?>">
                                <button type="submit" class="btn delete-btn">Delete</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="no-messages">No messages in your inbox.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Sent Mail Section -->
        <div id="sent" class="messages-section">
            <h2 class="section-title">Sent Mail</h2>
            <ul class="messages-list">
                <?php if (!empty($sentMessages)): ?>
                    <?php foreach ($sentMessages as $message): ?>
                        <li class="message-item">
                            <p><strong>To:</strong> <?php echo htmlspecialchars($message['Recipient_Name']); ?></p>
                            <p><?php echo htmlspecialchars($message['Message_Text']); ?></p>
                            <small><?php echo htmlspecialchars($message['Created_At']); ?></small>
                            <form method="POST" onsubmit="return confirmDelete(this);" class="delete-form">
                                <input type="hidden" name="delete_message_id"
                                    value="<?php echo htmlspecialchars($message['Message_ID']); ?>">
                                <button type="submit" class="btn delete-btn">Delete</button>
                            </form>

                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="no-messages">No sent messages.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Trash Section -->
        <div id="trash" class="messages-section">
            <h2 class="section-title">Trash</h2>
            <ul class="messages-list">
                <?php if (!empty($trashMessages)): ?>
                    <?php foreach ($trashMessages as $message): ?>
                        <li class="message-item">
    <p><strong>User:</strong> <?php echo htmlspecialchars($message['Other_User']); ?></p>
    <p><?php echo htmlspecialchars($message['Message_Text']); ?></p>
    <small><?php echo htmlspecialchars($message['Created_At']); ?></small>
    <form method="POST" class="restore-form">
        <input type="hidden" name="restore_message_id" value="<?php echo htmlspecialchars($message['Message_ID']); ?>">
        <button type="submit" class="btn restore-btn">Restore</button>
    </form>
    <form method="POST" class="delete-forever-form">
        <input type="hidden" name="delete_forever_message_id" value="<?php echo htmlspecialchars($message['Message_ID']); ?>">
        <button type="submit" class="btn delete-forever-btn">Delete Forever</button>
    </form>
</li>

                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="no-messages">No messages in the trash.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div id="confirmDeleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Deletion</h3>
            <p>Are you sure you want to delete this message?</p>
            <input type="hidden" id="deleteMessageId" value="">
            <button id="confirmDeleteButton" class="btn delete-btn">Yes, Delete</button>
            <button onclick="document.getElementById('confirmDeleteModal').style.display='none'"
                class="btn">Cancel</button>
        </div>
    </div>

    <script>
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

function validateMessageOwnership($messageID, $userId, $pdo) {
    $query = "
        SELECT Message_ID
        FROM messages
        WHERE Message_ID = :message_id AND (Sender_ID = :user_id OR Recipient_ID = :user_id)
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':message_id' => $messageID, ':user_id' => $userId]);

    return $stmt->fetch() !== false;
}

if (!validateMessageOwnership($messageID, $userId, $pdo)) {
    echo "<div class='error-message'>Invalid operation: Message does not belong to you.</div>";
    exit;
}


    </script>


    <?php include 'footer.php'; ?>
</body>

</html>