<?php
require 'database_connection.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view messages.");
}

$userId = intval($_SESSION['user_id']);
$section = $_GET['section'] ?? 'inbox';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar collapsible">
        <ul>
            <li class="<?= $section === 'inbox' ? 'active' : '' ?>">
                <a href="messages.php?section=inbox">Inbox</a>
            </li>
            <li class="<?= $section === 'sent' ? 'active' : '' ?>">
                <a href="messages.php?section=sent">Sent</a>
            </li>
            <li class="<?= $section === 'drafts' ? 'active' : '' ?>">
                <a href="messages.php?section=drafts">Drafts</a>
            </li>
            <li class="<?= $section === 'trash' ? 'active' : '' ?>">
                <a href="messages.php?section=trash">Trash</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="content">
        <button id="toggleSidebar" class="btn">☰ Toggle Sidebar</button>
        <?php
        // Load the selected section
        if ($section === 'inbox') {
            include 'inbox.php';
        } elseif ($section === 'sent') {
            include 'sent.php';
        } elseif ($section === 'drafts') {
            include 'drafts.php';
        } elseif ($section === 'trash') {
            include 'trash.php';
        } else {
            echo "<p>Select a section to view your messages.</p>";
        }
        ?>
    </div>

    <?php include 'footer.php'; ?>
    <script src="messaging.js" defer></script>
</body>
</html>
