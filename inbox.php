<?php
require 'database_connection.php';

$filter = $_GET['filter'] ?? 'all';

$inboxQuery = "
    SELECT m.Message_ID, m.Message_Text, m.Created_At, m.Read_Status, 
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

<div class="inbox-container">
    <header class="inbox-header">
        <h2>Inbox</h2>
        <label for="filter">Filter by:</label>
        <select id="filter" onchange="applyFilter()">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
            <option value="unread" <?= $filter === 'unread' ? 'selected' : '' ?>>Unread</option>
            <option value="read" <?= $filter === 'read' ? 'selected' : '' ?>>Read</option>
        </select>
    </header>

    <ul class="message-list">
        <?php foreach ($messages as $message): ?>
            <li class="message-item <?= $message['Read_Status'] ? 'read' : 'unread' ?>">
                <a href="view_message.php?message_id=<?= $message['Message_ID'] ?>" class="message-link">
                    <p><strong>From:</strong> <?= htmlspecialchars($message['Sender_Name']) ?></p>
                    <p><strong>Message:</strong> <?= htmlspecialchars(substr($message['Message_Text'], 0, 50)) ?>...</p>
                    <p><small>Sent: <?= htmlspecialchars($message['Created_At']) ?></small></p>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
    function applyFilter() {
        const filter = document.getElementById('filter').value;
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('filter', filter);
        window.location.href = `${window.location.pathname}?${urlParams.toString()}`;
    }
</script>
