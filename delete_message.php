<?php
require 'database_connection.php';

$messageID = $_POST['message_id'];

try {
    $updateQuery = "
        UPDATE messages
        SET Deleted_Status = 1
        WHERE Message_ID = :message_id
    ";
    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute([':message_id' => $messageID]);

    echo "Message moved to Trash successfully!";
    header("Location: messages.php");
} catch (PDOException $e) {
    die("Error deleting message: " . $e->getMessage());
}
?>
