<?php
require 'database_connection.php';

// Check if user is logged in
$user_id = intval($_SESSION['user_id'] ?? 0);
if (!$user_id) {
    die("You must be logged in to view your inbox.");
}

// Capture the filter and page from URL
$filter = $_GET['filter'] ?? 'all';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Base query for received messages
$receivedQuery = "
    SELECT m.Message_Text, m.Created_At, u.Name AS Sender_Name, l.Title AS Listing_Title, i.Image_URL AS Thumbnail_URL, m.Listing_ID
    FROM messages m
    JOIN user u ON m.Sender_ID = u.User_ID
    LEFT JOIN listings l ON m.Listing_ID = l.Listing_ID
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE m.Recipient_ID = ?
";

// Add filter conditions
if ($filter === 'sent') {
    $receivedQuery .= " AND m.Sender_ID = ?";
} elseif ($filter === 'unread') {
    $receivedQuery .= " AND m.Status = 'unread'";
} elseif ($filter === 'read') {
    $receivedQuery .= " AND m.Status = 'read'";
}

// Add pagination
$receivedQuery .= " LIMIT ? OFFSET ?";

// Prepare and execute the query
$receivedStmt = $conn->prepare($receivedQuery);
$receivedStmt->bind_param("iii", $user_id, $limit, $offset);
$receivedStmt->execute();
$receivedMessages = $receivedStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$receivedStmt->close();

// Group messages by listing
$groupedMessages = [];
foreach ($receivedMessages as $message) {
    $listingID = $message['Listing_ID'];
    if (!isset($groupedMessages[$listingID])) {
        $groupedMessages[$listingID] = [
            'title' => $message['Listing_Title'],
            'messages' => []
        ];
    }
    $groupedMessages[$listingID]['messages'][] = $message;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Your Inbox</h1>

    <!-- Filter Messages -->
    <label for="filter">Filter by:</label>
    <select id="filter" onchange="filterMessages()">
        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
        <option value="sent" <?= $filter === 'sent' ? 'selected' : '' ?>>Sent</option>
        <option value="unread" <?= $filter === 'unread' ? 'selected' : '' ?>>Unread</option>
        <option value="read" <?= $filter === 'read' ? 'selected' : '' ?>>Read</option>
    </select>

    <!-- Display Messages -->
    <?php foreach ($groupedMessages as $listingID => $listingData): ?>
        <h3><?= htmlspecialchars($listingData['title']) ?></h3>
        <?php foreach ($listingData['messages'] as $message): ?>
            <p><?= htmlspecialchars($message['Message_Text']) ?></p>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <!-- Pagination -->
    <div class="pagination">
        <a href="?page=<?= $page - 1 ?>" <?= $page <= 1 ? 'style="visibility:hidden;"' : '' ?>>Previous</a>
        <a href="?page=<?= $page + 1 ?>">Next</a>
    </div>

    <script>
        function filterMessages() {
            const filter = document.getElementById('filter').value;
            window.location.href = '?filter=' + filter;
        }
    </script>
</body>
</html>
