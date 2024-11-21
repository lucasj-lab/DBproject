<?php
require 'database_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senderId = $_POST['sender_id'];
    $receiverId = $_POST['receiver_id'];
    $messageText = $_POST['message_text'];
    $listingId = $_POST['listing_id'] ?? null;

    if ($senderId && $receiverId && $messageText) {
        $stmt = $pdo->prepare("
            INSERT INTO message (Message_Text, Date_Sent, Sender_ID, Receiver_ID, Listing_ID)
            VALUES (:message_text, NOW(), :sender_id, :receiver_id, :listing_id)
        ");
        $stmt->execute([
            'message_text' => $messageText,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'listing_id' => $listingId
        ]);

        echo json_encode(['success' => true, 'message' => 'Message sent successfully.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'All fields are required.']);
    }
}
?>
