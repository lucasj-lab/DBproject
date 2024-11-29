<?php
session_start();
require 'database_connection.php';

header('Content-Type: application/json');

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!$data || !isset($data['original_message_id'], $data['recipient_id'], $data['message_text'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input structure.']);
    exit;
}

$originalMessageId = intval($data['original_message_id']);
$recipientId = intval($data['recipient_id']);
$replyText = $data['message_text'];

if (!$originalMessageId || !$recipientId || !$replyText) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
    exit;
}

try {
    $query = "INSERT INTO replies (Message_ID, Sender_ID, Recipient_ID, Reply_Text, Created_At)
              VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Query preparation failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("iiis", $originalMessageId, $_SESSION['user_id'], $recipientId, $replyText);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Query execution failed: ' . $stmt->error]);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Reply sent successfully.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
