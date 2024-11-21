<?php
require 'database_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['notification_id'])) {
    $notificationId = filter_input(INPUT_GET, 'notification_id', FILTER_VALIDATE_INT);

    if ($notificationId) {
        $stmt = $pdo->prepare("UPDATE notification SET Is_Read = TRUE WHERE Notification_ID = :notification_id");
        $stmt->execute(['notification_id' => $notificationId]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid notification ID.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
}
?>
