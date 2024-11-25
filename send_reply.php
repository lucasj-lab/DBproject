<?php
require 'database_connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $originalMessageID = $_POST['original_message_id'] ?? null;
    $recipientID = $_POST['recipient_id'] ?? null;
    $messageText = $_POST['message_text'] ?? null;
    $senderID = $_SESSION['user_id'] ?? null;

    if (!$originalMessageID || !$recipientID || !$messageText || !$senderID) {
        echo "Error: Missing required fields.";
        exit;
    }

    try {
        // Insert the reply into the messages table
        $stmt = $pdo->prepare("
            INSERT INTO messages (Listing_ID, Sender_ID, Recipient_ID, Message_Text, Original_Message_ID)
            VALUES (NULL, :sender_id, :recipient_id, :message_text, :original_message_id)
        ");
        $stmt->execute([
            ':sender_id' => $senderID,
            ':recipient_id' => $recipientID,
            ':message_text' => $messageText,
            ':original_message_id' => $originalMessageID,
        ]);

        header("Location: messages.php?success=reply_sent");
        exit;
    } catch (PDOException $e) {
        echo "Error sending reply: " . htmlspecialchars($e->getMessage());
    }
}
?>
