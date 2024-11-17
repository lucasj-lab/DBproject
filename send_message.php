<?php
session_start();
require 'database_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['user_id'];
    $recipient_id = intval($_POST['recipient_id']);
    $message_text = trim($_POST['message_text']);

    if (empty($message_text)) {
        $_SESSION['message'] = "Message cannot be empty.";
        header("Location: user_profile.php?user_id=$recipient_id");
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO messages (Sender_ID, Recipient_ID, Message_Text) VALUES (?, ?, ?)");
        $stmt->execute([$sender_id, $recipient_id, $message_text]);

        $_SESSION['message'] = "Message sent successfully!";
        header("Location: user_profile.php?user_id=$recipient_id");
    } catch (Exception $e) {
        error_log("Message error: " . $e->getMessage());
        $_SESSION['message'] = "Failed to send message.";
        header("Location: user_profile.php?user_id=$recipient_id");
    }
}
?>
