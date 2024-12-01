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
</body>
</html>

<body>
    <header class="main-header">
        <!-- Hamburger Menu -->
        <div class="hamburger-menu" id="main-menu" aria-expanded="false" aria-label="Main menu" role="button" tabindex="0">
            <svg focusable="false" viewBox="0 0 24 24" class="hamburger-icon">
                <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"></path>
            </svg>
        </div>
    
        <!-- Search Bar -->
        <div class="search-container">
            <form class="search-form" role="search" aria-label="Search mail">
                <div class="search-input-container">
                    <input type="text" class="search-input" placeholder="Search mail" aria-label="Search mail">
                    <button type="submit" class="search-button" aria-label="Search">
                        <svg viewBox="0 0 24 24">
                            <path d="M20.49,19l-5.73-5.73C15.53,12.2,16,10.91,16,9.5C16,5.91,13.09,3,9.5,3S3,5.91,3,9.5C3,13.09,5.91,16,9.5,16c1.41,0,2.7-0.47,3.77-1.24L19,20.49L20.49,19z M5,9.5C5,7.01,7.01,5,9.5,5S14,7.01,14,9.5S11.99,14,9.5,14S5,11.99,5,9.5z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    
        <!-- Right: Icons -->
        <svg class="support-icon" viewBox="0 0 24 24" aria-label="Support">
            <path d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z"></path>
        </svg>
    
        <svg class="waffle-icon" viewBox="0 0 24 24" aria-label="Google apps">
            <path d="M6,8c1.1,0 2,-0.9 2,-2s-0.9,-2 -2,-2 -2,0.9 -2,2 0.9,2 2,2zM12,20c1.1,0 2,-0.9 2,-2s-0.9,-2 -2,-2 -2,0.9 -2,2 0.9,2 2,2zM6,20c1.1,0 2,-0.9 2,-2s-0.9,-2 -2,-2 -2,0.9 -2,2 0.9,2 2,2zM6,14c1.1,0 2,-0.9 2,-2s-0.9,-2 -2,-2 -2,0.9 -2,2 0.9,2 2,2zM12,14c1.1,0 2,-0.9 2,-2s-0.9,-2 -2,-2 -2,0.9 -2,2 0.9,2 2,2zM16,6c0,1.1 0.9,2 2,2s2,-0.9 2,-2 -0.9,-2 -2,-2 -2,0.9 -2,2zM12,8c1.1,0 2,-0.9 2,-2s-0.9,-2 -2,-2 -2,0.9 -2,2 0.9,2 2,2zM18,14c1.1,0 2,-0.9 2,-2s-0.9,-2 -2,-2 -2,0.9 -2,2 0.9,2 2,2zM18,20c1.1,0 2,-0.9 2,-2s-0.9,-2 -2,-2 -2,0.9 -2,2 0.9,2 2,2z"></path>
        </svg>
    
        <img class="user-icon" src="c:\Users\jlles\OneDrive - Kennesaw State University\Desktop\THE CORRECT PROJECT FOLDER FOR DB COURSE\DBproject\images\user-icon-white-black-back.svg" alt="User Icon">
    </header>

    <div class="main-content">
        <div class="navigation-container">
            <div class="nav-bar-wrapper">
                <nav class="nav-bar collapsed" aria-label="Main Navigation">
                    <table>
                        <tbody>
                            <tr>
                                <td class="icon">📥</td>
                                <td class="label"><a href="#inbox">Inbox</a></td>
                                <td class="count">986</td>
                            </tr>
                            <tr>
                                <td class="icon">⭐</td>
                                <td class="label"><a href="#starred">Starred</a></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="icon">✉️</td>
                                <td class="label"><a href="#sent">Sent</a></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="icon">📝</td>
                                <td class="label"><a href="#drafts">Drafts</a></td>
                                <td class="count">7</td>
                            </tr>
                            <tr>
                                <td class="icon">🗑️</td>
                                <td class="label"><a href="#trash">Trash</a></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="icon">🏷️</td>
                                <td class="label"><a href="#manage-labels">Manage Labels</a></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="icon">➕</td>
                                <td class="label"><a href="#new-labels">New Label</a></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </nav>
            </div>
            <div class="form-container">
                <div class="form-content">
                    <div class="sticky-headers">
                        <div class="header-actions">
                            <div class="filter-container">
                                <input type="checkbox" id="select-all">
                                <label for="select-all" class="filter-label">
                                    <span>▼</span>
                                </label>
                            </div>
                            <div class="bulk-actions">
                                <span class="icon trash" title="Delete">🗑️</span>
                                <span class="icon mark-read" title="Mark as Read">✓</span>
                            </div>
                        </div>
                        <div class="header-table">
                            <table>
                                <tr>
                                    <td class="tab active" id="primary-tab">Primary</td>
                                    <td class="tab" id="promotions-tab">Promotions</td>
                                    <td class="tab" id="social-tab">Social</td>
                                    <td class="tab" id="updates-tab">Updates</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="scroll-zone">
                        <div class="resizable-container">
                            <div class="messages-section">
                                <table class="messages-table">
                                    <thead>
                                        <tr>
                                            <th>Select</th>
                                            <th>Star</th>
                                            <th>From</th>
                                            <th>Subject</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="unread">
                                            <td><input type="checkbox" class="message-select"></td>
                                            <td>
                                                <input type="checkbox" id="star1" class="star-checkbox">
                                                <label for="star1" class="star-label">⭐</label>
                                            </td>
                                            <td>John Doe</td>
                                            <td>Meeting Reminder</td>
                                            <td>2024-11-30</td>
                                        </tr>
                                        <tr class="read">
                                            <td><input type="checkbox" class="message-select"></td>
                                            <td>
                                                <input type="checkbox" id="star2" class="star-checkbox">
                                                <label for="star2" class="star-label">⭐</label>
                                            </td>
                                            <td>Jane Smith</td>
                                            <td>Project Update</td>
                                            <td>2024-11-29</td>
                                        </tr>
                                        <tr class="unread">
                                            <td><input type="checkbox" class="message-select"></td>
                                            <td>
                                                <input type="checkbox" id="star3" class="star-checkbox">
                                                <label for="star3" class="star-label">⭐</label>
                                            </td>
                                            <td>Alex Johnson</td>
                                            <td>Invoice Details</td>
                                            <td>2024-11-28</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="resizable-divider"></div>
                            <div class="message-view-section">
                                <div class="message-view-header">
                                    <h2>Message View</h2>
                                </div>
                                <div class="message-view-content">
                                    <p>Select a message to view its content.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="messaging.js?v=<?php echo time(); ?>"></script>

    <?php include 'footer.php'; ?>
</body>
</html>