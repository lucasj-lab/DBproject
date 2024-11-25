<?php
require 'database_connection.php';
include 'header.php'; 

// Your messages.php logic starts here
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    echo "User ID: " . $userId;
} else {
    echo "User is not logged in.";
}

// Fetch Inbox Messages
$inboxQuery = "
    SELECT m.Message_ID, m.Message_Text, m.Created_At, u.Name AS Sender_Name
    FROM messages m
    JOIN user u ON m.Sender_ID = u.User_ID
    WHERE m.Recipient_ID = :user_id AND m.Deleted_Status = 0
    ORDER BY m.Created_At DESC
";
$inboxStmt = $pdo->prepare($inboxQuery);
$inboxStmt->execute([':user_id' => $userID]);
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
$sentStmt->execute([':user_id' => $userID]);
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
$trashStmt->execute([':user_id' => $userID]);
$trashMessages = $trashStmt->fetchAll(PDO::FETCH_ASSOC);

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
        $stmt->execute([':message_id' => $messageID, ':user_id' => $userID]);

        // Redirect to prevent re-submission
        header("Location: messages.php");
        exit;
    } catch (PDOException $e) {
        die("Error deleting message: " . $e->getMessage());
    }
}
include 'footer.php';
?>

<!DOCTYPE html>
< lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
</head>
<body>
    <h1>Messages</h1>
    
    <!-- Navigation Tabs -->
    <ul>
        <li><a href="#inbox">Inbox</a></li>
        <li><a href="#sent">Sent Mail</a></li>
        <li><a href="#trash">Trash</a></li>
    </ul>

    <!-- Inbox Section -->
    <div id="inbox">
        <h2>Inbox</h2>
        <ul>
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
        </ul>
    </div>

    <!-- Sent Mail Section -->
    <div id="sent">
        <h2>Sent Mail</h2>
        <ul>
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
        </ul>
    </div>

    <!-- Trash Section -->
    <div id="trash">
        <h2>Trash</h2>
        <ul>
            <?php foreach ($trashMessages as $message): ?>
                <li>
                    <strong>User:</strong> <?php echo htmlspecialchars($message['Other_User']); ?><br>
                    <?php echo htmlspecialchars($message['Message_Text']); ?><br>
                    <small><?php echo $message['Created_At']; ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
