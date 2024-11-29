<?php
require 'database_connection.php';

try {
    // Fetch all drafts for the logged-in user (assuming `Recipient_ID` is the user ID)
    $userId = $_SESSION['user_id']; // Replace with actual session variable for logged-in user
    $query = "SELECT 
                  messages.Message_ID, 
                  messages.Subject, 
                  messages.Message_Text, 
                  messages.Created_At, 
                  user.Name AS Sender_Name
              FROM messages
              JOIN user ON messages.Sender_ID = user.User_ID
              WHERE messages.Recipient_ID = ? AND messages.Draft_Status = 1
              ORDER BY messages.Created_At DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if drafts exist
    $drafts = [];
    while ($row = $result->fetch_assoc()) {
        $drafts[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    echo "<p>Error fetching drafts: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drafts</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="drafts-container">
        <h2>Your Drafts</h2>
        <?php if (empty($drafts)): ?>
            <p>No drafts available.</p>
        <?php else: ?>
            <ul class="drafts-list">
                <?php foreach ($drafts as $draft): ?>
                    <li class="draft-item">
                        <h3><?php echo htmlspecialchars($draft['Subject'] ?: "No Subject"); ?></h3>
                        <p><strong>From:</strong> <?php echo htmlspecialchars($draft['Sender_Name']); ?></p>
                        <p><strong>Created At:</strong> <?php echo htmlspecialchars($draft['Created_At']); ?></p>
                        <p><?php echo nl2br(htmlspecialchars(substr($draft['Message_Text'], 0, 100))) . '...'; ?></p>
                        <a href="view_message.php?message_id=<?php echo $draft['Message_ID']; ?>" class="btn">View</a>
                        <a href="delete_draft.php?message_id=<?php echo $draft['Message_ID']; ?>" class="btn btn-danger">Delete</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
