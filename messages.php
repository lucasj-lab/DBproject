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
    <header class="main-header">
        <!-- Hamburger Menu -->
        
    
        <!-- Search Bar -->
        <div class="search-container">
            <form class="search-form" role="search" aria-label="Search mail">
                <div class="search-input-container">
                    <input type="text" class="search-input" placeholder="Search mail" aria-label="Search mail">

                </div>
            </form>
        </div>
    
        <!-- Right: Icons -->
        <svg class="support-icon" viewBox="0 0 24 24" aria-label="Support">
        </svg>
    
        <svg class="waffle-icon" viewBox="0 0 24 24" aria-label="Google apps">
          
        </svg>
    
       
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