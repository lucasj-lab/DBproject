<?php
require 'database_connection.php';
include 'header.php';

// Ensure user is logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id']; // Correctly defined variable
} else {
    echo "Error: User is not logged in.";
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
    die("Error fetching messages: " . $e->getMessage());
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
        die("Error deleting message: " . $e->getMessage());
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
    <h1>Messages</h1>

    <!-- Navigation Tabs -->
    <ul class="tabs">
        <li><a href="#inbox">Inbox</a></li>
        <li><a href="#sent">Sent Mail</a></li>
        <li><a href="#trash">Trash</a></li>
    </ul>

    <!-- Inbox Section -->
    <div id="inbox">
        <h2>Inbox</h2>
        <ul>
            <?php if (!empty($inboxMessages)): ?>
                <?php foreach ($inboxMessages as $message): ?>
                    <li>
                        <strong>From:</strong> <?php echo htmlspecialchars($message['Sender_Name']); ?><br>
                        <?php echo htmlspecialchars($message['Message_Text']); ?><br>
                        <small><?php echo $message['Created_At']; ?></small><br>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete_message_id" value="<?php echo $message['Message_ID']; ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No messages in your inbox.</li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Sent Mail Section -->
    <div id="sent">
        <h2>Sent Mail</h2>
        <ul>
            <?php if (!empty($sentMessages)): ?>
                <?php foreach ($sentMessages as $message): ?>
                    <li>
                        <strong>To:</strong> <?php echo htmlspecialchars($message['Recipient_Name']); ?><br>
                        <?php echo htmlspecialchars($message['Message_Text']); ?><br>
                        <small><?php echo $message['Created_At']; ?></small><br>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete_message_id" value="<?php echo $message['Message_ID']; ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No sent messages.</li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Trash Section -->
    <div id="trash">
        <h2>Trash</h2>
        <ul>
            <?php if (!empty($trashMessages)): ?>
                <?php foreach ($trashMessages as $message): ?>
                    <li>
                        <strong>User:</strong> <?php echo htmlspecialchars($message['Other_User']); ?><br>
                        <?php echo htmlspecialchars($message['Message_Text']); ?><br>
                        <small><?php echo $message['Created_At']; ?></small>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No messages in the trash.</li>
            <?php endif; ?>
        </ul>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
