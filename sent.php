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
           u.Name AS Recipient_Name, l.Title AS Listing_Title, i.Image_URL
    FROM messages m
    LEFT JOIN user u ON m.Recipient_ID = u.User_ID
    LEFT JOIN listings l ON m.Listing_ID = l.Listing_ID
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE m.Sender_ID = ? AND m.Deleted_Status = 0
";

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

<!-- Display Sent Messages -->
<table class="email-table">
    <thead>
        <tr>
            <th>Recipient</th>
            <th>Listing</th>
            <th>Message</th>
            <th>Actions</th>
        </tr>
    </thead>
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
                        <button onclick="viewMessage(<?= $message['Message_ID'] ?>)">View</button>
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
