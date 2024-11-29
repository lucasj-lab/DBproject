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
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <title>Messages</title>
</head>
<body>
    <div class="gb_Vc"> <!-- Main container -->
        <div class="gb_Qc"> <!-- Sidebar container -->
            <div class="gb_hd gb_id"> <!-- Sidebar header -->
                <h2 class="gb_qe">Messages</h2>
            </div>
            <ul class="sidebar-list">
                <li class="sidebar-item <?= $section === 'inbox' ? 'active' : '' ?>">
                    <a href="messages.php?section=inbox" class="gb_re">Inbox</a>
                </li>
                <li class="sidebar-item <?= $section === 'sent' ? 'active' : '' ?>">
                    <a href="messages.php?section=sent" class="gb_re">Sent</a>
                </li>
                <li class="sidebar-item <?= $section === 'drafts' ? 'active' : '' ?>">
                    <a href="messages.php?section=drafts" class="gb_re">Drafts</a>
                </li>
                <li class="sidebar-item <?= $section === 'trash' ? 'active' : '' ?>">
                    <a href="messages.php?section=trash" class="gb_re">Trash</a>
                </li>
            </ul>
        </div>
        <div class="gb_Ha"> <!-- Content container -->
            <div class="content-header">
                <button id="toggleSidebar" class="gb_Da">☰</button>
                <h2><?php echo ucfirst($section); ?></h2>
            </div>
            <div class="content-body">
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
    </div>
    <?php include 'footer.php'; ?>
    <script src="messaging.js" defer></script>
</body>
</html>
