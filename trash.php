<?php
require 'database_connection.php';

if (!isset($userId)) {
    die("User ID is not set.");
}

// Determine filter
$filter = $_GET['filter'] ?? 'all';

// Base query for trash messages
$trashQuery = "
    SELECT m.Message_ID, m.Subject, m.Message_Text, m.Created_At, 
           u.Name AS Sender_Name, l.Title AS Listing_Title, i.Image_URL, m.Read_Status
    FROM messages m
    LEFT JOIN user u ON m.Sender_ID = u.User_ID
    LEFT JOIN listings l ON m.Listing_ID = l.Listing_ID
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE m.Recipient_ID = ? AND m.Deleted_Status = 1
";

// Apply filter for read/unread messages
if ($filter === 'unread') {
    $trashQuery .= " AND m.Read_Status = 'unread'";
} elseif ($filter === 'read') {
    $trashQuery .= " AND m.Read_Status = 'read'";
}

// Add sorting
$trashQuery .= " ORDER BY m.Created_At DESC";

// Execute the query
$trashStmt = $conn->prepare($trashQuery);
$trashStmt->bind_param("i", $userId);
$trashStmt->execute();
$trashMessages = $trashStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$trashStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trash</title>
    <link rel="stylesheet" href="styles.css">
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
</head>
<body>
    <h2>Trash</h2>

    <!-- Filter Dropdown -->
    <label for="filter">Filter by:</label>
    <select id="filter" onchange="applyFilter()">
        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
        <option value="unread" <?= $filter === 'unread' ? 'selected' : '' ?>>Unread</option>
        <option value="read" <?= $filter === 'read' ? 'selected' : '' ?>>Read</option>
    </select>

    <!-- Display Trash Messages -->
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
            <?php if (!empty($trashMessages)): ?>
                <?php foreach ($trashMessages as $message): ?>
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
                            <button onclick="openWarningModal(<?= $message['Message_ID'] ?>, 'restore')">Restore</button>
                            <button onclick="openWarningModal(<?= $message['Message_ID'] ?>, 'delete_forever')">Delete Forever</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No messages in the trash.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <script src="messaging.js"></script>
</body>
    
