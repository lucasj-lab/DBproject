<?php
require 'database_connection.php';

$messageId = intval($_GET['message_id'] ?? 0);

$query = "
    SELECT m.Subject, m.Message_Text, m.Created_At, u.Name AS Sender_Name
    FROM messages m
    JOIN user u ON m.Sender_ID = u.User_ID
    WHERE m.Message_ID = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $messageId);
$stmt->execute();
$message = $stmt->get_result()->fetch_assoc();

if (!$message) {
    echo "Message not found.";
    exit;
}

echo "<h2>From: " . htmlspecialchars($message['Sender_Name']) . "</h2>";
echo "<h3>Subject: " . htmlspecialchars($message['Subject']) . "</h3>";
echo "<p>" . nl2br(htmlspecialchars($message['Message_Text'])) . "</p>";
echo "<p><strong>Received:</strong> " . htmlspecialchars($message['Created_At']) . "</p>";
?>
