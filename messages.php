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
    <div class="main-container"> <!-- Main container -->
        <aside class="sidebar">
            <header class="sidebar-header">
                <h2>Messages</h2>
            </header>
            <nav>
                <ul class="sidebar-menu">
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
            </nav>
        </aside>

        <main class="content">
            <header class="content-header">
                <button id="toggleSidebar" class="menu-toggle">☰</button>
                <h1><?= ucfirst($section) ?></h1>
            </header>
            <section class="content-body">
                <?php
                switch ($section) {
                    case 'inbox':
                        include 'inbox.php';
                        break;
                    case 'sent':
                        include 'sent.php';
                        break;
                    case 'drafts':
                        include 'drafts.php';
                        break;
                    case 'trash':
                        include 'trash.php';
                        break;
                    default:
                        echo "<p>Please select a section to view messages.</p>";
                }
                ?>
            </section>
        </main>
    </div>
    <?php include 'footer.php'; ?>
    <script src="messaging.js" defer></script>
</body>
</html>
