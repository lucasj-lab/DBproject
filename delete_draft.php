<?php
require 'database_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $messageId = intval($_GET['message_id'] ?? 0);

    if (!$messageId) {
        echo "<p>Invalid message ID.</p>";
        exit;
    }

    try {
        // Delete draft
        $query = "DELETE FROM messages WHERE Message_ID = ? AND Draft_Status = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $messageId);
        $stmt->execute();

        header("Location: drafts.php?message=Draft deleted successfully");
        exit;
    } catch (Exception $e) {
        echo "<p>Error deleting draft: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>
