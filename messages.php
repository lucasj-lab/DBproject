<?php
require 'database_connection.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
}
$userId = intval($_SESSION['user_id']);
if (isset($_SESSION['message'])) {
    echo "<div class='session-message {$_SESSION['message_type']}'>" . htmlspecialchars($_SESSION['message']) . "</div>";
    unset($_SESSION['message'], $_SESSION['message_type']); // Clear after displaying
}
// Determine which section to load
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

<div class="main-content">
    <?php
    // Load the selected section
    if ($section === 'inbox') {
        include 'inbox.php';
    } elseif ($section === 'sent') {
        include 'sent.php'; 
    } elseif ($section === 'trash') {
        include 'trash.php';
    } elseif ($section === 'drafts') {
        include 'drafts.php'; // Drafts logic will be in this file
    } else {
        echo "<p>Select a section to view your messages.</p>";
    }
    ?>
</div>
<script src="messaging.js"></script>
</body>
<footer>
    <?php include 'footer.php'; ?>
</footer>
</html>
