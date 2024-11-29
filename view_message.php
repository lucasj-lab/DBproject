<?php
require 'database_connection.php';

// Fetch the message ID from the URL or handle invalid requests
$messageId = intval($_GET['message_id'] ?? 0);
if (!$messageId) {
    if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        echo json_encode(['success' => false, 'error' => 'Invalid message ID.']);
        exit;
    }
    die("Invalid message ID.");
}

// Mark the message as read
$updateQuery = "UPDATE messages SET Read_Status = 1 WHERE Message_ID = ?";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("i", $messageId);
$stmt->execute();
$stmt->close();

// Fetch the main message details
$messageQuery = "
    SELECT messages.Message_Text, messages.Created_At, 
           sender.Name AS Sender_Name 
    FROM messages
    JOIN user AS sender ON messages.Sender_ID = sender.User_ID
    WHERE messages.Message_ID = ?";
$stmt = $conn->prepare($messageQuery);
$stmt->bind_param("i", $messageId);
$stmt->execute();
$result = $stmt->get_result();
$message = $result->fetch_assoc();
$stmt->close();

if (!$message) {
    if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        echo json_encode(['success' => false, 'error' => 'Message not found.']);
        exit;
    }
    echo "<p>Message not found.</p>";
    exit;
}

// Fetch replies for the message
$repliesQuery = "
    SELECT replies.Reply_Text, replies.Created_At, sender.Name AS Sender_Name 
    FROM replies
    JOIN user AS sender ON replies.Sender_ID = sender.User_ID
    WHERE replies.Message_ID = ?
    ORDER BY replies.Created_At ASC";
$stmt = $conn->prepare($repliesQuery);
$stmt->bind_param("i", $messageId);
$stmt->execute();
$replies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle AJAX requests: Return JSON data
if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    echo json_encode([
        'success' => true,
        'message' => [
            'Message_Text' => nl2br(htmlspecialchars($message['Message_Text'])),
            'Created_At' => date('M d, Y H:i:s', strtotime($message['Created_At'])),
            'Sender_Name' => htmlspecialchars($message['Sender_Name']),
            'Replies' => array_map(function ($reply) {
                return [
                    'Reply_Text' => nl2br(htmlspecialchars($reply['Reply_Text'])),
                    'Created_At' => date('M d, Y H:i:s', strtotime($reply['Created_At'])),
                    'Sender_Name' => htmlspecialchars($reply['Sender_Name']),
                ];
            }, $replies)
        ]
    ]);
    exit;
}

// Include the template for rendering the message view
include 'message_view_template.php';
?>
