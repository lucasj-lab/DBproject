<?php
require 'database_connection.php'; // Ensure this initializes a $mysqli connection object

// Get the original message and recipient details from the URL
$originalMessageID = $_GET['message_id'] ?? null;
$recipientID = $_GET['recipient_id'] ?? null;

if (!$originalMessageID || !$recipientID) {
    echo "Error: Missing information to reply to the message.";
    exit;
}

// Fetch the original message (for context)
$query = "
    SELECT m.Message_Text, u.Name AS Sender_Name
    FROM messages m
    JOIN user u ON m.Sender_ID = u.User_ID
    WHERE m.Message_ID = ?
";

if ($stmt = $mysqli->prepare($query)) {
    $stmt->bind_param('i', $originalMessageID); // Bind the parameter as an integer
    $stmt->execute();
    $result = $stmt->get_result();
    $originalMessage = $result->fetch_assoc();
    $stmt->close();
} else {
    echo "Error preparing the query: " . htmlspecialchars($mysqli->error);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Message</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    
<?php include 'header.php'; ?>

    <div class="reply-message-container">
        <h1 class="page-title">Reply to Message</h1>
        
        <div class="original-message">
            <p><strong>From:</strong> <?php echo htmlspecialchars($originalMessage['Sender_Name']); ?></p>
            <p><?php echo htmlspecialchars($originalMessage['Message_Text']); ?></p>
        </div>

        <form action="send_reply.php" method="POST" class="reply-message-form">
            <input type="hidden" name="original_message_id" value="<?php echo htmlspecialchars($originalMessageID); ?>">
            <input type="hidden" name="recipient_id" value="<?php echo htmlspecialchars($recipientID); ?>">

            <div class="form-group">
                <label for="message_text" class="form-label">Your Reply:</label>
                <textarea name="message_text" id="message_text" class="message-textarea" rows="5" placeholder="Type your reply here..." required></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn send-reply-btn">Send Reply</button>
            </div>
        </form>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
