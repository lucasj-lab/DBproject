<?php
require 'database_connection.php';
include 'header.php';

// Ensure user is logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
} else {
    echo "<div class='error-message'>Error: User is not logged in.</div>";
    exit;
}

// Initialize message arrays
$inboxMessages = [];
$sentMessages = [];
$trashMessages = [];

// Fetch Inbox Messages
$inboxQuery = "
    SELECT m.Message_ID, m.Message_Text, m.Created_At, u.Name AS Sender_Name, 
           l.Title AS Listing_Title, l.Listing_ID, i.Image_URL
    FROM messages m
    JOIN user u ON m.Sender_ID = u.User_ID
    JOIN listings l ON m.Listing_ID = l.Listing_ID
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE m.Recipient_ID = ? AND m.Deleted_Status = 0
    ORDER BY m.Created_At DESC
";

if ($stmt = $conn->prepare($inboxQuery)) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $inboxMessages = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch Sent Messages
$sentQuery = "
    SELECT m.Message_ID, m.Message_Text, m.Created_At, u.Name AS Recipient_Name, 
           l.Title AS Listing_Title, l.Listing_ID, i.Image_URL
    FROM messages m
    JOIN user u ON m.Recipient_ID = u.User_ID
    JOIN listings l ON m.Listing_ID = l.Listing_ID
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE m.Sender_ID = ? AND m.Deleted_Status = 0
    ORDER BY m.Created_At DESC
";

if ($stmt = $conn->prepare($sentQuery)) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $sentMessages = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch Trash Messages
$trashQuery = "
    SELECT m.Message_ID, m.Message_Text, m.Created_At, 
           IF(m.Sender_ID = ?, u.Name, 'You') AS Other_User,
           l.Title AS Listing_Title, l.Listing_ID, i.Image_URL
    FROM messages m
    JOIN user u ON (m.Sender_ID = u.User_ID OR m.Recipient_ID = u.User_ID)
    JOIN listings l ON m.Listing_ID = l.Listing_ID
    LEFT JOIN images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE (m.Sender_ID = ? OR m.Recipient_ID = ?) AND m.Deleted_Status = 1
    ORDER BY m.Created_At DESC
";

if ($stmt = $conn->prepare($trashQuery)) {
    $stmt->bind_param("iii", $userId, $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $trashMessages = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Handle delete, restore, and delete forever actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_message_id'])) {
        $messageID = intval($_POST['delete_message_id']);
        $updateQuery = "
            UPDATE messages
            SET Deleted_Status = 1
            WHERE Message_ID = ? AND (Sender_ID = ? OR Recipient_ID = ?)
        ";
        if ($stmt = $conn->prepare($updateQuery)) {
            $stmt->bind_param("iii", $messageID, $userId, $userId);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: messages.php");
        exit;
    }

    if (isset($_POST['restore_message_id'])) {
        $messageID = intval($_POST['restore_message_id']);
        $updateQuery = "
            UPDATE messages
            SET Deleted_Status = 0
            WHERE Message_ID = ? AND Recipient_ID = ?
        ";
        if ($stmt = $conn->prepare($updateQuery)) {
            $stmt->bind_param("ii", $messageID, $userId);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: messages.php");
        exit;
    }

    if (isset($_POST['delete_forever_message_id'])) {
        $messageID = intval($_POST['delete_forever_message_id']);
        $deleteQuery = "
            DELETE FROM messages
            WHERE Message_ID = ? AND Recipient_ID = ?
        ";
        if ($stmt = $conn->prepare($deleteQuery)) {
            $stmt->bind_param("ii", $messageID, $userId);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: messages.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Platform</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .email-thumbnail img {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            margin-right: 10px;
            vertical-align: middle;
        }
        .email-thumbnail span {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="email-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="email-nav">
                <li onclick="showSection('inbox')">Inbox</li>
                <li onclick="showSection('sent')">Sent</li>
                <li onclick="showSection('trash')">Trash</li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Inbox Section -->
            <div id="inbox" class="email-section">
                <h2>Inbox</h2>
                <table class="email-table">
                    <thead>
                        <tr>
                            <th>Listing</th>
                            <th>Message</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inboxMessages as $message): ?>
                            <tr onclick="viewMessage('<?php echo htmlspecialchars($message['Message_ID']); ?>')">
                                <td>
                                    <div class="email-thumbnail">
                                        <?php if (!empty($message['Image_URL'])): ?>
                                            <img src="<?php echo htmlspecialchars($message['Image_URL']); ?>" alt="Thumbnail">
                                        <?php else: ?>
                                            <span>No Thumbnail</span>
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($message['Listing_Title'] ?? 'No Title'); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars(substr($message['Message_Text'], 0, 50)); ?>...</td>
                                <td><button>View</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Sent Section -->
            <div id="sent" class="email-section" style="display:none;">
                <h2>Sent</h2>
                <table class="email-table">
                    <thead>
                        <tr>
                            <th>Listing</th>
                            <th>Message</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sentMessages as $message): ?>
                            <tr>
                                <td>
                                    <div class="email-thumbnail">
                                        <?php if (!empty($message['Image_URL'])): ?>
                                            <img src="<?php echo htmlspecialchars($message['Image_URL']); ?>" alt="Thumbnail">
                                        <?php else: ?>
                                            <span>No Thumbnail</span>
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($message['Listing_Title'] ?? 'No Title'); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars(substr($message['Message_Text'], 0, 50)); ?>...</td>
                                <td><button>View</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Trash Section -->
            <div id="trash" class="email-section" style="display:none;">
                <h2>Trash</h2>
                <table class="email-table">
                    <thead>
                        <tr>
                            <th>Listing</th>
                            <th>Message</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trashMessages as $message): ?>
                            <tr>
                                <td>
                                    <div class="email-thumbnail">
                                        <?php if (!empty($message['Image_URL'])): ?>
                                            <img src="<?php echo htmlspecialchars($message['Image_URL']); ?>" alt="Thumbnail">
                                        <?php else: ?>
                                            <span>No Thumbnail</span>
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($message['Listing_Title'] ?? 'No Title'); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars(substr($message['Message_Text'], 0, 50)); ?>...</td>
                                <td><button>View</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.email-section');
            sections.forEach(section => section.style.display = 'none');
            document.getElementById(sectionId).style.display = 'block';
        }
    </script>
</body>
</html>
