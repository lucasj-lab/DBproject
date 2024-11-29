<?php
require 'database_connection.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to delete messages.");
}

$messageId = intval($_GET['message_id']);

$deleteQuery = "
    UPDATE messages 
    SET Deleted_Status = 1 
    WHERE Message_ID = ? AND Recipient_ID = ?
";

$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("ii", $messageId, $_SESSION['user_id']);
if ($stmt->execute()) {
    header("Location: messages.php?section=trash");
    exit;
} else {
    die("Error deleting message.");
}
