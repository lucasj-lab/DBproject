<?php
require 'database_connection.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view your inbox.");
}

$userId = intval($_SESSION['user_id']);

// Filter parameter
$filter = $_GET['filter'] ?? 'all';

// Base query for inbox messages
$inboxQuery = "
    SELECT m.Message_ID, m.Subject, m.Message_Text, m.Created_At, m.Read_Status, 
           u.Name AS Sender_Name, l.Title AS Listing_Title, i.Image_URL
    FROM messages m
    LEFT JOIN user u ON m.Sender_ID = u.User_ID
    LEFT JOIN listings l ON m.Listing_ID = l.Listing_ID
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE m.Recipient_ID = ? AND m.Deleted_Status = 0
";

// Apply the filter based on `Read_Status`
if ($filter === 'unread') {
    $inboxQuery .= " AND m.Read_Status = 0"; // Unread messages
} elseif ($filter === 'read') {
    $inboxQuery .= " AND m.Read_Status = 1"; // Read messages
}

// Add sorting
$inboxQuery .= " ORDER BY m.Created_At DESC";

// Execute the query
$inboxStmt = $conn->prepare($inboxQuery);
$inboxStmt->bind_param("i", $userId);
$inboxStmt->execute();
$inboxMessages = $inboxStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$inboxStmt->close();
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
        const urlParams = new URLSearchParams(window.location.search);

        // Set the filter parameter
        urlParams.set('filter', filter);

        // Reload the page with the updated URL
        window.location.href = `${window.location.pathname}?${urlParams.toString()}`;
    }
</script>

<!-- Display Inbox Messages -->
<table class="email-table">
    <thead>
        <tr>
            <th>Sender</th>
            <th>Listing</th>
            <th>Message</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($inboxMessages)): ?>
            <?php foreach ($inboxMessages as $message): ?>
                <tr>
                    <td><?= htmlspecialchars($message['Sender_Name']) ?></td>
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
                <td colspan="4">No messages found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script src="messaging.js"></script>
