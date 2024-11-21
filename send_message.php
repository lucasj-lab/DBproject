<?php
require 'database_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senderId = filter_input(INPUT_POST, 'sender_id', FILTER_VALIDATE_INT);
    $receiverId = filter_input(INPUT_POST, 'receiver_id', FILTER_VALIDATE_INT);
    $messageText = trim($_POST['message_text'] ?? '');

    if ($senderId && $receiverId && !empty($messageText)) {
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                INSERT INTO message (Message_Text, Date_Sent, Sender_ID, Receiver_ID)
                VALUES (:message_text, NOW(), :sender_id, :receiver_id)
            ");
            $stmt->execute([
                'message_text' => $messageText,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);

            $notificationStmt = $pdo->prepare("
                INSERT INTO notification (Notification_Text, Date_Sent, User_ID)
                VALUES (:notification_text, NOW(), :user_id)
            ");
            $notificationStmt->execute([
                'notification_text' => "You have a new message from User ID $senderId.",
                'user_id' => $receiverId
            ]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Message sent successfully.']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Message could not be sent.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid data.']);
    }
}
?>
