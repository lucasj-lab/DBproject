<?php
require 'database_connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$messageID = intval($data['message_id'] ?? 0);
$action = $data['action'] ?? 'delete';

try {
    if ($action === 'delete') {
        $query = "UPDATE messages SET Deleted_Status = 1 WHERE Message_ID = :message_id";
    } elseif ($action === 'restore') {
        $query = "UPDATE messages SET Deleted_Status = 0 WHERE Message_ID = :message_id";
    } elseif ($action === 'delete_forever') {
        $query = "DELETE FROM messages WHERE Message_ID = :message_id";
    } else {
        throw new Exception("Invalid action");
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute([':message_id' => $messageID]);

    echo json_encode(['success' => true, 'message' => ucfirst($action) . " action completed successfully!"]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => "Error handling message: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
