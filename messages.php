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

    <div class="messages-main-header">
        <!-- Hamburger Menu -->
        <div class="messages-hamburger-menu" id="main-menu" aria-expanded="false" aria-label="Main menu" role="button"
            tabindex="0">
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
                            <path
                                d="M20.49,19l-5.73-5.73C15.53,12.2,16,10.91,16,9.5C16,5.91,13.09,3,9.5,3S3,5.91,3,9.5C3,13.09,5.91,16,9.5,16c1.41,0,2.7-0.47,3.77-1.24L19,20.49L20.49,19z M5,9.5C5,7.01,7.01,5,9.5,5S14,7.01,14,9.5S11.99,14,9.5,14S5,11.99,5,9.5z">
                            </path>
                        </svg>
                    </button>
                    <!-- Right: Icons -->
                    <svg class="messages-support-icon" viewBox="0 0 24 24" aria-label="Support">
                        <path
                            d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z">
                        </path>
                    </svg>

                    <svg class="messages-waffle-icon" viewBox="0 0 24 24" aria-label="Google apps">
                        <path
                            d="M6,8c1.1,0 2,-0.9 2,-2s-0.9,-2 -2,-2 -2,0.9 -2,2 0.9,2 2,2zM12,20c1.1,0 2,-0.9 2,-2s-0.9,-2 -2,-2 -2,0.9 -2,2 0.9,2 2,2zM6,20c1.1,0 2,-0.9 2,-2s-0.9,-2 -2,-2 -2,0.9 -2,2 0.9,2 2,2zM6,14c1.1,0 2,-0.9 2,-2s-0.9,-2 -2,-2 -2,0.9 -2,2 0.9,2 2,2zM12,14c1.1,0 2,-0.9 2,-2s-0.9,-2 -2,-2 -2,0.9 -2,2 0.9,2 2,2zM16,6c0,1.1 0.9,2 2,2s2,-0.9 2,-2 -0.9,-2 -2,-2 -2,0.9 -2,2zM12,8c1.1,0 2,-0.9 2,-2s-0.9,-2 -2,-2 -2,0.9 -2,2 0.9,2 2,2zM18,14c1.1,0 2,-0.9 2,-2s-0.9,-2 -2,-2 -2,0.9 -2,2 0.9,2 2,2zM18,20c1.1,0 2,-0.9 2,-2s-0.9,-2 -2,-2 -2,0.9 -2,2 0.9,2 2,2z">
                        </path>
                    </svg>
                </div>
        </div>
        </form>
    </div>

    <div class="messages-main-content">
        <div class="messages-navigation-container">
            <div class="messages-nav-bar-wrapper">
                <nav class="messages-nav-bar collapsed" aria-label="Main Navigation">
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
            <div class="messages-form-container">
                <div class="messages-form-content">
                    <div class="messages-sticky-headers">
                        <div class="messages-header-actions">
                            <div class="messages-filter-container">
                                <input type="checkbox" id="select-all">
                                <label for="select-all" class="filter-label">
                                    <span>▼</span>
                                </label>
                            </div>
                            <div class="messages-bulk-actions">
                                <span class="icon trash" title="Delete">🗑️</span>
                                <span class="icon mark-read" title="Mark as Read">✓</span>
                            </div>
                        </div>
                        <div class="messages-header-table">
                            <table>
                                <tr>
                                    <td class="messages-tab active" id="messages-primary-tab">Primary</td>
                                    <td class="messages-tab" id="promotions-tab">Promotions</td>
                                    <td class="messages-tab" id="social-tab">Social</td>
                                    <td class="messages-tab" id="updates-tab">Updates</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="messages-scroll-zone">
                        <div class="messages-resizable-container">
                            <div class="messages-messages-section">
                                <table class="messages-messages-table">
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
                                        <tr class="messages-unread">
                                            <td><input type="checkbox" class="message-select"></td>
                                            <td>
                                                <input type="checkbox" id="star1" class="star-checkbox">
                                                <label for="star1" class="star-label">⭐</label>
                                            </td>
                                            <td>John Doe</td>
                                            <td>Meeting Reminder</td>
                                            <td>2024-11-30</td>
                                        </tr>
                                        <tr class="messages-read">
                                            <td><input type="checkbox" class="message-select"></td>
                                            <td>
                                                <input type="checkbox" id="star2" class="star-checkbox">
                                                <label for="star2" class="star-label">⭐</label>
                                            </td>
                                            <td>Jane Smith</td>
                                            <td>Project Update</td>
                                            <td>2024-11-29</td>
                                        </tr>
                                        <tr class="messages-unread">
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
                            <div class="messages-resizable-divider"></div>
                            <div class="messages-message-view-section">
                                <div class="messages-message-view-header">
                                    <h2>Message View</h2>
                                </div>
                                <div class="messages-message-view-content">
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