<?php
require 'database_connection.php';

if (!isset($userId)) {
    die("User ID is not set.");
}

// Determine filter
$filter = $_GET['filter'] ?? 'all';

// Base query for sent messages
$sentQuery = "
    SELECT m.Message_ID, m.Subject, m.Message_Text, m.Created_At, 
           u.Name AS Recipient_Name, l.Title AS Listing_Title, i.Image_URL, m.Read_Status
    FROM messages m
    LEFT JOIN user u ON m.Recipient_ID = u.User_ID
    LEFT JOIN listings l ON m.Listing_ID = l.Listing_ID
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE m.Sender_ID = ? AND m.Deleted_Status = 0
";

// Apply filter for read/unread messages
if ($filter === 'unread') {
    $sentQuery .= " AND m.Read_Status = 'unread'";
} elseif ($filter === 'read') {
    $sentQuery .= " AND m.Read_Status = 'read'";
}

// Add sorting
$sentQuery .= " ORDER BY m.Created_At DESC";

// Execute the query
$sentStmt = $conn->prepare($sentQuery);
$sentStmt->bind_param("i", $userId);
$sentStmt->execute();
$sentMessages = $sentStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$sentStmt->close();
?>

<h2>Sent Messages</h2>

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
        const urlParams = new URLSearchParams(window.location.search);

        // Set the filter parameter
        urlParams.set('filter', filter);

        // Reload the page with the updated URL
        window.location.href = `${window.location.pathname}?${urlParams.toString()}`;
    }
</script>

<!-- Display Sent Messages -->
<table class="email-table">
    <thead>
        <tr>
            <th>Recipient</th>
            <th>Listing</th>
            <th>Message</th>
            <th>Actions</th>
        </tr>
    </thead><?php
require 'database_connection.php';

$sentQuery = "
    SELECT m.Message_ID, m.Message_Text, m.Created_At, 
           u.Name AS Recipient_Name 
    FROM messages m
    JOIN user u ON m.Recipient_ID = u.User_ID
    WHERE m.Sender_ID = ? AND m.Deleted_Status = 0
    ORDER BY m.Created_At DESC
";

$stmt = $conn->prepare($sentQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="sent-container">
    <header class="sent-header">
        <h2>Sent</h2>
    </header>

    <ul class="message-list">
        <?php foreach ($messages as $message): ?>
            <li class="message-item">
                <a href="view_message.php?message_id=<?= $message['Message_ID'] ?>" class="message-link">
                    <p><strong>To:</strong> <?= htmlspecialchars($message['Recipient_Name']) ?></p>
                    <p><strong>Message:</strong> <?= htmlspecialchars(substr($message['Message_Text'], 0, 50)) ?>...</p>
                    <p><small>Sent: <?= htmlspecialchars($message['Created_At']) ?></small></p>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

    <tbody>
        <?php if (!empty($sentMessages)): ?>
            <?php foreach ($sentMessages as $message): ?>
                <tr>
                    <td><?= htmlspecialchars($message['Recipient_Name']) ?></td>
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
                        <button onclick="viewMessage(<?= $message['Message_ID'] ?>)">View Conversation</button>
                        <button onclick="openWarningModal(<?= $message['Message_ID'] ?>, 'delete')">Delete</button>
                    </td>

                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No sent messages found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
<script src="messaging.js"></script>
</body>