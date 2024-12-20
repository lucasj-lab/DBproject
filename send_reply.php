<?php
session_start();
require 'database_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    $_SESSION['error'] = 'You must be logged in to reply.';
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Debugging the input
file_put_contents('debug_log.txt', json_encode($data) . PHP_EOL, FILE_APPEND);

if (!$data || !isset($data['original_message_id'], $data['recipient_id'], $data['message_text'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input structure.']);
    $_SESSION['error'] = 'Invalid input structure.';
    exit;
}

$originalMessageId = intval($data['original_message_id']);
$recipientId = intval($data['recipient_id']);
$replyText = $data['message_text'];

if (!$originalMessageId || !$recipientId || !$replyText) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
    $_SESSION['error'] = 'Missing required fields.';
    exit;
}

try {
    $query = "INSERT INTO replies (Message_ID, Sender_ID, Recipient_ID, Reply_Text, Created_At)
              VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiis", $originalMessageId, $_SESSION['user_id'], $recipientId, $replyText);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Query execution failed: ' . $stmt->error]);
        $_SESSION['error'] = 'Could not save reply. Please try again.';
        exit;
    }

    $_SESSION['message'] = 'Reply sent successfully!';
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    $_SESSION['error'] = 'An error occurred while saving the reply.';
}
?>
