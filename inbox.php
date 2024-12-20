<?php
$filter = $_GET['filter'] ?? 'all';

$inboxQuery = "
    SELECT m.Message_ID, m.Message_Text, m.Created_At, m.Read_Status, m.Has_Attachment,
           u.Name AS Sender_Name 
    FROM messages m
    JOIN user u ON m.Sender_ID = u.User_ID
    WHERE m.Recipient_ID = ? AND m.Deleted_Status = 0
";

if ($filter === 'unread') {
    $inboxQuery .= " AND m.Read_Status = 0";
} elseif ($filter === 'read') {
    $inboxQuery .= " AND m.Read_Status = 1";
}

$inboxQuery .= " ORDER BY m.Created_At DESC";

$stmt = $conn->prepare($inboxQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<table class="message-table">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectAllCheckbox" title="Select All"></th>
            <th>Sender</th>
            <th>Message</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($messages as $message): ?>
        <tr>
            <td><input type="checkbox" class="messageCheckbox" value="<?= $message['Message_ID'] ?>"></td>
            <td><?= htmlspecialchars($message['Sender_Name']) ?></td>
            <td><?= htmlspecialchars(substr($message['Message_Text'], 0, 50)) ?>...</td>
            <td><?= date('M d, Y', strtotime($message['Created_At'])) ?></td>
            <td>
                <a href="delete_message.php?message_id=<?= $message['Message_ID'] ?>">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
