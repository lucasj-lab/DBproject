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
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="main-header">
        <div class="hamburger-menu">
            <svg class="hamburger-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M3 6h18M3 12h18M3 18h18"></path>
            </svg>
        </div>
        <div class="search-container">
            <div class="search-input-container">
                <input type="text" class="search-input" placeholder="Search messages...">
                <button class="search-button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M11 2a9 9 0 015.64 16H21l-4-4h-2v-2h-4v2H7v2H3v-6a9 9 0 0118 0 9 9 0 11-9-9z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="navigation-container">
        <nav class="nav-bar">
            <ul>
                <li class="<?= $section === 'inbox' ? 'active' : '' ?>">
                    <a href="messages.php?section=inbox">
                        <span class="icon">📥</span>
                        <span class="label">Inbox</span>
                    </a>
                </li>
                <li class="<?= $section === 'sent' ? 'active' : '' ?>">
                    <a href="messages.php?section=sent">
                        <span class="icon">📤</span>
                        <span class="label">Sent</span>
                    </a>
                </li>
                <li class="<?= $section === 'drafts' ? 'active' : '' ?>">
                    <a href="messages.php?section=drafts">
                        <span class="icon">✏️</span>
                        <span class="label">Drafts</span>
                    </a>
                </li>
                <li class="<?= $section === 'trash' ? 'active' : '' ?>">
                    <a href="messages.php?section=trash">
                        <span class="icon">🗑️</span>
                        <span class="label">Trash</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="form-container">
            <div class="form-content">
                <div class="sticky-headers">
                    <h1><?= ucfirst($section) ?> Messages</h1>
                </div>
                <div class="scroll-zone">
                    <table class="messages-table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Star</th>
                                <th>From</th>
                                <th>Subject</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="messages-tbody">
                            <?php
                            switch ($section) {
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
                                    include 'inbox.php';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script src="messaging.js?v=<?php echo time(); ?>"></script>
</body>
</html>