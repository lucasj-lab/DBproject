<?php
require 'database_connection.php'; // Ensure this file initializes $conn (MySQLi connection)

$messageID = intval($_POST['message_id']);
$action = $_POST['action'] ?? 'delete'; // Default action is 'delete'

try {
    if ($action === 'delete') {
        $query = "UPDATE messages SET Deleted_Status = 1 WHERE Message_ID = ?";
    } elseif ($action === 'restore') {
        $query = "UPDATE messages SET Deleted_Status = 0 WHERE Message_ID = ?";
    } elseif ($action === 'delete_forever') {
        $query = "DELETE FROM messages WHERE Message_ID = ?";
    } else {
        die("Invalid action");
    }

    $stmt = $conn->prepare($query); // Use MySQLi instead of PDO
    $stmt->bind_param("i", $messageID);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => ucfirst($action) . " action completed successfully!"]);
    exit;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => "Error handling message: " . $e->getMessage()]);
    exit;
}
?>
