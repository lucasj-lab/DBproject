<?php
require 'database_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $originalMessageID = intval($_POST['original_message_id'] ?? 0);
    $recipientID = intval($_POST['recipient_id'] ?? 0);
    $messageText = trim($_POST['message_text'] ?? '');
    $senderID = intval($_SESSION['user_id'] ?? 0);

    if (!$recipientID || !$messageText || !$senderID) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
        exit;
    }

    try {
        $query = "INSERT INTO drafts (Sender_ID, Recipient_ID, Message_Text, Original_Message_ID, Created_At)
                  VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iisi", $senderID, $recipientID, $messageText, $originalMessageID);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Draft saved successfully.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error saving draft: ' . $e->getMessage()]);
    }
}
?>
