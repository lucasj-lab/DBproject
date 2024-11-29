<?php
require 'database_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $originalMessageId = intval($data['original_message_id'] ?? 0);
    $recipientId = intval($data['recipient_id'] ?? 0);
    $messageText = $data['message_text'] ?? '';

    if (!$originalMessageId || !$recipientId || !$messageText) {
        echo json_encode(['success' => false, 'error' => 'Invalid input data.']);
        exit;
    }

    try {
        // Insert the reply
        $insertReplyQuery = "INSERT INTO replies (Message_ID, Recipient_ID, Reply_Text, Created_At) 
                             VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($insertReplyQuery);
        $stmt->bind_param("iis", $originalMessageId, $recipientId, $messageText);
        $stmt->execute();
        $stmt->close();

        // Update the original message
        $updateMessageQuery = "UPDATE messages 
                               SET Draft_Status = 0, Read_Status = 1 
                               WHERE Message_ID = ?";
        $stmt = $conn->prepare($updateMessageQuery);
        $stmt->bind_param("i", $originalMessageId);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['success' => true, 'message' => 'Reply sent successfully.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error sending reply: ' . $e->getMessage()]);
    }
}
?>
