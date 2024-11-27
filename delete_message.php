<?php
require 'database_connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$messageID = intval($data['message_id'] ?? 0);
$action = $data['action'] ?? 'delete';

// Validate input
if (!$messageID || !in_array($action, ['delete', 'restore', 'delete_forever'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input.']);
    exit;
}

try {
    if ($action === 'delete') {
        $query = "UPDATE messages SET Deleted_Status = 1 WHERE Message_ID = ?";
    } elseif ($action === 'restore') {
        $query = "UPDATE messages SET Deleted_Status = 0 WHERE Message_ID = ?";
    } elseif ($action === 'delete_forever') {
        $query = "DELETE FROM messages WHERE Message_ID = ?";
    }

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $messageID);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => ucfirst($action) . " action completed successfully!"]);
    } else {
        echo json_encode(['success' => false, 'error' => "Failed to execute action."]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => "Error handling message: " . $e->getMessage()]);
}

// Close the database connection
$conn->close();
?>
