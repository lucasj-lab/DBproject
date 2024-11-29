<?php
require 'database_connection.php';

$data = json_decode(file_get_contents('php://input'), true);

$originalMessageId = intval($data['original_message_id'] ?? 0);
$recipientId = intval($data['recipient_id'] ?? 0);
$replyText = $data['message_text'] ?? '';

if (!$originalMessageId || !$recipientId || !$replyText) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data.']);
    exit;
}

try {
    $query = "INSERT INTO replies (Message_ID, Sender_ID, Recipient_ID, Reply_Text, Created_At)
              VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiis", $originalMessageId, $_SESSION['user_id'], $recipientId, $replyText);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Reply sent successfully.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
