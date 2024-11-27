<?php
session_start();
require 'database_connection.php';

// Validate user login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in.']);
    exit;
}

$userId = intval($_SESSION['user_id']);
$recipientId = intval($_POST['recipient_id'] ?? 0);
$subject = trim($_POST['subject'] ?? '');
$messageText = trim($_POST['message_text'] ?? '');
$listingId = intval($_POST['listing_id'] ?? 0);

// Validate inputs
if (!$messageText || !$userId) {
    echo json_encode(['success' => false, 'error' => 'Message text is required.']);
    exit;
}

// Save or update draft
$query = "
    INSERT INTO drafts (User_ID, Recipient_ID, Subject, Message_Text, Listing_ID)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        Recipient_ID = VALUES(Recipient_ID),
        Subject = VALUES(Subject),
        Message_Text = VALUES(Message_Text),
        Listing_ID = VALUES(Listing_ID),
        Created_At = NOW()
";
$stmt = $conn->prepare($query);
$stmt->bind_param("iissi", $userId, $recipientId, $subject, $messageText, $listingId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Draft saved.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save draft.']);
}

$stmt->close();
$conn->close();
?>
