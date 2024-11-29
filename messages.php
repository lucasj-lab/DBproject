<?php
require 'database_connection.php';
include 'header.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view your messages.");
}

$userId = intval($_SESSION['user_id']);

// Display session messages (e.g., success or error notifications)
if (isset($_SESSION['message'])) {
    echo "<div class='session-message {$_SESSION['message_type']}'>" . htmlspecialchars($_SESSION['message']) . "</div>";
    unset($_SESSION['message'], $_SESSION['message_type']); // Clear after displaying
}

// Determine which section and filter to load
$section = $_GET['section'] ?? 'inbox';
$filter = $_GET['filter'] ?? 'all'; // Optional filter parameter
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="styles.css">
    <script src="messaging.js"></script>
</head>
<body>
    <div class="messages-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <ul>
                <li class="<?= $section === 'inbox' ? 'active' : '' ?>">
                <a href="messages.php?section=inbox&filter=<?= htmlspecialchars($filter) ?>">Inbox</a>

                </li>
                <li class="<?= $section === 'sent' ? 'active' : '' ?>">
                    <a href="messages.php?section=sent&filter=<?= htmlspecialchars($filter) ?>">Sent</a>
                </li>
                <li class="<?= $section === 'trash' ? 'active' : '' ?>">
                    <a href="messages.php?section=trash&filter=<?= htmlspecialchars($filter) ?>">Trash</a>
                </li>
                <li class="<?= $section === 'drafts' ? 'active' : '' ?>">
                    <a href="messages.php?section=drafts">Drafts</a>
                </li>
            </ul>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <?php
            // Include the selected section
            switch ($section) {
                case 'inbox':
                    include 'inbox.php';
                    break;
                case 'sent':
                    include 'sent.php';
                    break;
                case 'trash':
                    include 'trash.php';
                    break;
                case 'drafts':
                    include 'drafts.php';
                    break;
                default:
                    echo "<p>Select a section to view your messages.</p>";
            }
            ?>
        </div>
    </div>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>
</html>