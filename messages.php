<?php
require 'database_connection.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view messages.");
}

$userId = intval($_SESSION['user_id']);
$section = $_GET['section'] ?? 'inbox';

// Verify if 'Has_Attachment' exists in the database schema.
$messagesQuery = "
    SELECT m.Message_ID, m.Message_Text, m.Created_At, m.Read_Status, 
           IFNULL(m.Has_Attachment, 0) AS Has_Attachment, -- Ensure default if column is missing
           u.Name AS Sender_Name 
    FROM messages m
    JOIN user u ON m.Sender_ID = u.User_ID
    WHERE m.Recipient_ID = ? AND m.Deleted_Status = 0
    ORDER BY m.Created_At DESC
";

$stmt = $conn->prepare($messagesQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <title>Messages</title>
</head>
<body>
    <div class="main-container">
        <aside class="sidebar">
            <header class="sidebar-header">
                <h2>Messages</h2>
            </header>
            <nav>
                <ul class="sidebar-menu">
                    <li class="<?= $section === 'inbox' ? 'active' : '' ?>"><a href="messages.php?section=inbox">Inbox</a></li>
                    <li class="<?= $section === 'sent' ? 'active' : '' ?>"><a href="messages.php?section=sent">Sent</a></li>
                    <li class="<?= $section === 'drafts' ? 'active' : '' ?>"><a href="messages.php?section=drafts">Drafts</a></li>
                    <li class="<?= $section === 'trash' ? 'active' : '' ?>"><a href="messages.php?section=trash">Trash</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <header class="content-header">
                <button id="toggleSidebar" class="menu-toggle">☰</button>
                <h1><?= ucfirst($section) ?></h1>
            </header>
            <section class="content-body">
                <table class="message-table" role="grid">
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>Sender</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $message): ?>
                            <tr class="zA <?= $message['Read_Status'] ? 'read' : 'unread' ?>" tabindex="-1" role="row">
                                <td class="select-cell">
                                    <div role="checkbox" aria-checked="false" tabindex="-1" class="checkbox-container">
                                        <input type="checkbox" name="selectMessage[]" value="<?= $message['Message_ID'] ?>">
                                    </div>
                                </td>
                                
                                <td class="starred-cell">
                                    <span class="star-icon" role="button" title="Not starred">
                                        <img src="images/cleardot.gif" alt="Star">
                                    </span>
                                </td>

                                <td class="importance-cell">
                                    <div role="switch" aria-checked="false" title="Mark as important" class="importance-icon"></div>
                                </td>

                                <td class="sender-cell">
                                    <?= htmlspecialchars($message['Sender_Name']) ?>
                                </td>

                                <td class="message-preview-cell">
                                    <div>
                                        <span class="message-title"><?= htmlspecialchars(substr($message['Message_Text'], 0, 50)) ?></span>
                                        <span class="message-snippet"> - <?= htmlspecialchars(substr($message['Message_Text'], 50, 100)) ?></span>
                                    </div>
                                </td>

                                <td class="timestamp-cell">
                                    <?= date('M d', strtotime($message['Created_At'])) ?>
                                </td>

                                <td class="attachment-cell">
                                    <?php if ($message['Has_Attachment']): ?>
                                        <span class="attachment-icon" title="Has attachment">
                                            <svg width="20" height="20" viewBox="0 0 24 24"><path d="M18 16.5H7.5c-2.21 0-4-1.79-4-4s1.79-4 4-4H18a2.5 2.5 0 0 1 0 5H8.5c-.55 0-1-.45-1-1s.45-1 1-1H18V10H8.5a2.5 2.5 0 0 0 0 5H18c2.21 0 4-1.79 4-4s-1.79-4-4-4H7.5C4.46 7 2 9.46 2 12.5S4.46 18 7.5 18H18v-1.5z"></path></svg>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="actions-cell">
                                    <ul class="action-menu">
                                        <li><button title="Archive">🗄️</button></li>
                                        <li><button title="Delete">🗑️</button></li>
                                        <li><button title="Mark as unread">📩</button></li>
                                    </ul>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
    <?php include 'footer.php'; ?>
    <script src="messaging.js" defer></script>
</body>
</html>
