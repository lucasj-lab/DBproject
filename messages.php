<?php
require 'database_connection.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view messages.");
}

$userId = intval($_SESSION['user_id']);
$section = $_GET['section'] ?? 'inbox';
$sidebarType = $_GET['sidebar'] ?? 'collapsible'; // Sidebar type parameter
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
    <div class="main-container">
        <!-- Sidebar Type Selection -->
        <div class="sidebar-selector">
            <label for="sidebarType">Choose Sidebar:</label>
            <select id="sidebarType" onchange="changeSidebar()">
                <option value="collapsible" <?= $sidebarType === 'collapsible' ? 'selected' : '' ?>>Collapsible</option>
                <option value="fixed" <?= $sidebarType === 'fixed' ? 'selected' : '' ?>>Fixed</option>
                <option value="offcanvas" <?= $sidebarType === 'offcanvas' ? 'selected' : '' ?>>Off-Canvas</option>
            </select>
        </div>

        <!-- Sidebar -->
        <div class="sidebar <?= $sidebarType ?>">
            <ul>
                <li><a href="messages.php?section=inbox" class="<?= $section === 'inbox' ? 'active' : '' ?>">Inbox</a></li>
                <li><a href="messages.php?section=sent" class="<?= $section === 'sent' ? 'active' : '' ?>">Sent</a></li>
                <li><a href="messages.php?section=drafts" class="<?= $section === 'drafts' ? 'active' : '' ?>">Drafts</a></li>
                <li><a href="messages.php?section=trash" class="<?= $section === 'trash' ? 'active' : '' ?>">Trash</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="content">
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
    </div>

    <?php include 'footer.php'; ?>
    <script src="messaging.js" defer></script>
</body>
</html>
