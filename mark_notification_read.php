<?php
require 'database_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $messageId = intval($_POST['message_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if (!$messageId || !$action) {
        echo json_encode(['success' => false, 'error' => 'Invalid request.']);
        exit;
    }

    try {
        if ($action === 'restore') {
            $query = "UPDATE messages SET Deleted_Status = 0 WHERE Message_ID = ?";
        } elseif ($action === 'delete_forever') {
            $query = "DELETE FROM messages WHERE Message_ID = ?";
        } else {
            echo json_encode(['success' => false, 'error' => 'Unknown action.']);
            exit;
        }

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $messageId);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Action completed successfully.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error processing request: ' . $e->getMessage()]);
    }
}
?>
