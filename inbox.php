<?php
require 'database_connection.php';

if (!isset($userId)) {
    die("User ID is not set.");
}

// Determine filter
$filter = $_GET['filter'] ?? 'all';

// Base query for received messages
$receivedQuery = "
    SELECT m.Message_ID, m.Subject, m.Message_Text, m.Created_At, m.Read_Status, 
           u.Name AS Sender_Name, l.Title AS Listing_Title, i.Image_URL
    FROM messages m
    LEFT JOIN user u ON m.Sender_ID = u.User_ID
    LEFT JOIN listings l ON m.Listing_ID = l.Listing_ID
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE m.Recipient_ID = ? AND m.Deleted_Status = 0
";

// Apply filter conditions
if ($filter === 'unread') {
    $receivedQuery .= " AND m.Read_Status = 'unread'";
} elseif ($filter === 'read') {
    $receivedQuery .= " AND m.Read_Status = 'read'";
}

// Add sorting
$receivedQuery .= " ORDER BY m.Created_At DESC";

// Execute the query
$receivedStmt = $conn->prepare($receivedQuery);
$receivedStmt->bind_param("i", $userId);
$receivedStmt->execute();
$receivedMessages = $receivedStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$receivedStmt->close();
?>

<h2>Inbox</h2>

<!-- Filter Dropdown -->
<label for="filter">Filter by:</label>
<select id="filter" onchange="applyFilter()">
    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
    <option value="unread" <?= $filter === 'unread' ? 'selected' : '' ?>>Unread</option>
    <option value="read" <?= $filter === 'read' ? 'selected' : '' ?>>Read</option>
</select>

<script>
    function applyFilter() {
        const filter = document.getElementById('filter').value;
        window.location.href = 'messages.php?section=inbox&filter=' + filter;
    }
</script>

<!-- Display Messages -->
<table class="email-table">
    <thead>
        <tr>
            <th>Listing</th>
            <th>Message</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($receivedMessages)): ?>
            <?php foreach ($receivedMessages as $message): ?>
                <tr>
                    <td>
                        <div class="email-thumbnail">
                            <?php if (!empty($message['Image_URL'])): ?>
                                <img src="<?= htmlspecialchars($message['Image_URL']) ?>" alt="Thumbnail">
                            <?php else: ?>
                                <span>No Thumbnail</span>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($message['Listing_Title'] ?? 'No Title') ?></span>
                        </div>
                    </td>
                    <td><?= htmlspecialchars(substr($message['Message_Text'], 0, 50)) ?>...</td>
                    <td>
                        <button onclick="viewMessage(<?= $message['Message_ID'] ?>)">View</button>
                        <button onclick="openWarningModal(<?= $message['Message_ID'] ?>, 'delete')">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">No messages found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
<script src="messaging.js"></script>

</body>
</html>
