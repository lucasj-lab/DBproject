<?php
require 'database_connection.php';

if (!isset($userId)) {
    die("User ID is not set.");
}

// Base query for trash messages
$trashQuery = "
    SELECT m.Message_ID, m.Subject, m.Message_Text, m.Created_At, 
           u.Name AS Sender_Name, l.Title AS Listing_Title, i.Image_URL
    FROM messages m
    LEFT JOIN user u ON m.Sender_ID = u.User_ID
    LEFT JOIN listings l ON m.Listing_ID = l.Listing_ID
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE m.Recipient_ID = ? AND m.Deleted_Status = 1
";

// Add sorting
$trashQuery .= " ORDER BY m.Created_At DESC";

// Execute the query
$trashStmt = $conn->prepare($trashQuery);
$trashStmt->bind_param("i", $userId);
$trashStmt->execute();
$trashMessages = $trashStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$trashStmt->close();
?>

<h2>Trash</h2>

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
