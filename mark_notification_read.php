<?php
require 'database_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $messageId = intval($_POST['message_id'] ?? 0);

    if (!$messageId) {
        echo json_encode(['success' => false, 'error' => 'Invalid message ID.']);
        exit;
    }

    try {
        // Mark the message as read
        $markReadQuery = "UPDATE messages SET Read_Status = 1 WHERE Message_ID = ?";
        $stmt = $conn->prepare($markReadQuery);
        $stmt->bind_param("i", $messageId);
        $stmt->execute();
        $stmt->close();

        // Check if a reply exists
        $checkReplyQuery = "SELECT COUNT(*) AS Reply_Count FROM replies WHERE Message_ID = ?";
        $stmt = $conn->prepare($checkReplyQuery);
        $stmt->bind_param("i", $messageId);
        $stmt->execute();
        $result = $stmt->get_result();
        $replyData = $result->fetch_assoc();
        $stmt->close();

        // If no reply exists, mark as draft
        if ($replyData['Reply_Count'] == 0) {
            $markDraftQuery = "UPDATE messages SET Draft_Status = 1 WHERE Message_ID = ?";
            $stmt = $conn->prepare($markDraftQuery);
            $stmt->bind_param("i", $messageId);
            $stmt->execute();
            $stmt->close();
        }

        echo json_encode(['success' => true, 'message' => 'Message updated successfully.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error updating message: ' . $e->getMessage()]);
    }
}
?>
