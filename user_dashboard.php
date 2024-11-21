<?php
session_start();
require 'database_connection.php';
require 'listing_queries.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}



$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT Name, Email, Date_Joined FROM user WHERE User_ID = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);


// Fetch user's listings with thumbnail
$sql = "
    SELECT 
        l.Listing_ID, 
        l.Title, 
        l.Description, 
        l.Price, 
        l.Date_Posted, 
        l.City, 
        l.State, 
        i.Image_URL AS Thumbnail_Image
    FROM 
        listings l
    LEFT JOIN 
        images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE 
        l.User_ID = :user_id
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 400px;
            text-align: center;
        }

        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .btn-danger {
            background-color: #dc3545;
            color: #fff;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
        }

        .thumbnail-image {
            max-width: 100px;
            height: auto;
        }
    </style>
</head>






<?php
session_start();
require 'database_connection.php';

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT Name, Email, Date_Joined FROM user WHERE User_ID = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's listings with thumbnails
$listingsStmt = $pdo->prepare("
    SELECT 
        l.Listing_ID, 
        l.Title, 
        l.Description, 
        l.Price, 
        l.Date_Posted, 
        l.City, 
        l.State, 
        i.Image_URL AS Thumbnail_Image
    FROM 
        listings l
    LEFT JOIN 
        images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE 
        l.User_ID = :user_id
");
$listingsStmt->execute(['user_id' => $user_id]);
$listings = $listingsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch notifications
$notificationStmt = $pdo->prepare("
    SELECT Notification_ID, Notification_Text, Date_Sent
    FROM notification
    WHERE User_ID = :user_id
    ORDER BY Date_Sent DESC
");
$notificationStmt->execute(['user_id' => $user_id]);
$notifications = $notificationStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch messages
$messageStmt = $pdo->prepare("
    SELECT 
        m.Message_ID,
        m.Message_Text,
        m.Date_Sent,
        u1.Name AS Sender_Username,
        u2.Name AS Receiver_Username
    FROM message m
    JOIN user u1 ON m.Sender_ID = u1.User_ID
    JOIN user u2 ON m.Receiver_ID = u2.User_ID
    WHERE m.Receiver_ID = :user_id
    ORDER BY m.Date_Sent DESC
");
$messageStmt->execute(['user_id' => $user_id]);
$messages = $messageStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <main>
        <h1>Welcome, <?php echo htmlspecialchars($user['Name']); ?></h1>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
        <p><strong>Member Since:</strong> <?php echo htmlspecialchars((new DateTime($user['Date_Joined']))->format('l, F jS, Y')); ?></p>

        <!-- Notifications Section -->
        <section>
            <h2>Notifications</h2>
            <?php if (!empty($notifications)): ?>
                <ul>
                    <?php foreach ($notifications as $notification): ?>
                        <li>
                            <p><?php echo htmlspecialchars($notification['Notification_Text']); ?></p>
                            <small><?php echo htmlspecialchars((new DateTime($notification['Date_Sent']))->format('M d, Y h:i A')); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No notifications found.</p>
            <?php endif; ?>
        </section>

        <!-- Inbox Section -->
        <section>
            <h2>Inbox</h2>
            <?php if (!empty($messages)): ?>
                <ul>
                    <?php foreach ($messages as $message): ?>
                        <li>
                            <p><strong>From:</strong> <?php echo htmlspecialchars($message['Sender_Username']); ?></p>
                            <p><?php echo nl2br(htmlspecialchars($message['Message_Text'])); ?></p>
                            <small>Sent at: <?php echo htmlspecialchars($message['Date_Sent']); ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No messages found.</p>
            <?php endif; ?>
        </section>

        <!-- Listings Section -->
        <section>
            <h2>Your Listings</h2>
            <?php if (!empty($listings)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>City</th>
                            <th>State</th>
                            <th>Thumbnail</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listings as $listing): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($listing['Title']); ?></td>
                                <td><?php echo htmlspecialchars($listing['Description']); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($listing['Price'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($listing['City']); ?></td>
                                <td><?php echo htmlspecialchars($listing['State']); ?></td>
                                <td>
                                    <img src="<?php echo htmlspecialchars($listing['Thumbnail_Image'] ?? 'placeholder.jpg'); ?>" alt="Thumbnail" style="max-width: 100px;">
                                </td>
                                <td>
                                    <a href="edit_listing.php?listing_id=<?php echo $listing['Listing_ID']; ?>">Edit</a>
                                    <button onclick="showDeleteModal(<?php echo $listing['Listing_ID']; ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No listings found.</p>
            <?php endif; ?>
        </section>
    </main>

    <!-- Delete Modal -->
    <div id="deleteModal" style="display:none;">
        <div>
            <h2>Delete Listing</h2>
            <p>Are you sure you want to delete this listing?</p>
            <button id="confirmDelete">Confirm</button>
            <button id="cancelDelete">Cancel</button>
        </div>
    </div>

    <script>
        const deleteModal = document.getElementById('deleteModal');
        let currentListingId = null;

        function showDeleteModal(listingId) {
            currentListingId = listingId;
            deleteModal.style.display = 'block';
        }

        document.getElementById('cancelDelete').onclick = () => {
            deleteModal.style.display = 'none';
        };

        document.getElementById('confirmDelete').onclick = () => {
            fetch(`delete_listing.php?listing_id=${currentListingId}`, { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Listing deleted successfully.');
                        location.reload();
                    } else {
                        alert('Error deleting listing: ' + data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
        };
    </script>

<style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 400px;
            text-align: center;
        }

        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .btn-danger {
            background-color: #dc3545;
            color: #fff;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
        }

        .thumbnail-image {
            max-width: 100px;
            height: auto;
        }
    </style>
    
</body>
</html>

<body>
    <?php include 'header.php'; ?>

    <main class="dope-dashboard">
        <h1 class="dashboard-title">Welcome, <?php echo htmlspecialchars($user['Name']); ?></h1>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
        <p><strong>Member Since:</strong>
            <?php echo htmlspecialchars((new DateTime($user['Date_Joined']))->format('l, F jS, Y')); ?>
        </p>

        <h2>Your Listings</h2>

        <?php if (!empty($listings)): ?>
            <div class="table-container">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Date Posted</th>
                            <th>City</th>
                            <th>State</th>
                            <th>Thumbnail</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listings as $listing): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($listing['Title']); ?></td>
                                <td><?php echo htmlspecialchars($listing['Description']); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($listing['Price'], 2)); ?></td>
                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        !empty($listing['Date_Posted'])
                                            ? (new DateTime($listing['Date_Posted']))->format('l, F jS, Y')
                                            : 'Date not available'
                                    );
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($listing['City']); ?></td>
                                <td><?php echo htmlspecialchars($listing['State']); ?></td>
                                <td class="thumbnail-cell">
                                    <img src="<?= htmlspecialchars($listing['Thumbnail_Image'] ?? 'placeholder.jpg'); ?>"
                                         alt="Listing Thumbnail" class="thumbnail-image">
                                </td>
                                <td class="action-buttons-cell">
                                    <a href="edit_listing.php?listing_id=<?= $listing['Listing_ID']; ?>"
                                       class="pill-button">Edit</a>
                                    <button class="pill-button delete-button" onclick="showDeleteModal(<?= $listing['Listing_ID']; ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="btn-container"><a href="create_listing.php">New Listing</a></div>
        <?php else: ?>
            <p>You have no listings yet. <a href="create_listing.php" class="pill-button">Create one here</a>.</p>
        <?php endif; ?>
    </main>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h2>Delete Listing</h2>
            <p>Are you sure you want to delete this listing? This action cannot be undone.</p>
            <div class="modal-actions">
                <button id="confirmDelete" class="btn btn-danger">Delete</button>
                <button id="cancelDelete" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
    <?php
require 'database_connection.php';

// Fetch messages for the logged-in user
$userId = $_SESSION['user_id']; // Ensure the user is logged in and `user_id` is in the session

$stmt = $pdo->prepare("
    SELECT 
        m.Message_ID,
        m.Message_Text,
        m.Date_Sent,
        u1.Name AS Sender_Username, -- Use the correct column name
        u2.Name AS Receiver_Username -- Use the correct column name
    FROM message m
    JOIN user u1 ON m.Sender_ID = u1.User_ID
    JOIN user u2 ON m.Receiver_ID = u2.User_ID
    WHERE m.Receiver_ID = :user_id
    ORDER BY m.Date_Sent DESC
");
$stmt->execute(['user_id' => $userId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="message-inbox">
    <h1>Inbox</h1>
    
    <?php if (!empty($messages)): ?>
        <div class="messages-list">
            <?php foreach ($messages as $message): ?>
                <div class="message-item">
                    <p><strong>From:</strong> <?php echo htmlspecialchars($message['Sender_Username']); ?></p>
                    <p><strong>To:</strong> <?php echo htmlspecialchars($message['Receiver_Username']); ?></p>
                    <p><strong>Message:</strong> <?php echo nl2br(htmlspecialchars($message['Message_Text'])); ?></p>
                    <p><small>Sent at: <?php echo htmlspecialchars($message['Date_Sent']); ?></small></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No messages found.</p>
    <?php endif; ?>

    <a href="send_message.php">Send a new message</a>
</div>

    <script>
        const deleteModal = document.getElementById('deleteModal');
        const confirmDeleteButton = document.getElementById('confirmDelete');
        const cancelDeleteButton = document.getElementById('cancelDelete');
        let currentListingId = null;

        function showDeleteModal(listingId) {
            currentListingId = listingId;
            deleteModal.style.display = 'block';
        }

        cancelDeleteButton.onclick = () => {
            deleteModal.style.display = 'none';
            currentListingId = null;
        };

        confirmDeleteButton.onclick = () => {
            if (currentListingId) {
                fetch(`delete_listing.php?listing_id=${currentListingId}`, { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Listing deleted successfully.');
                            location.reload();
                        } else {
                            alert('Error deleting listing: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the listing.');
                    })
                    .finally(() => {
                        deleteModal.style.display = 'none';
                        currentListingId = null;
                    });
            }
        };

        window.onclick = (event) => {
            if (event.target === deleteModal) {
                deleteModal.style.display = 'none';
                currentListingId = null;
            }
        };
    </script>

<div id="dashboardNotification">
    <?php if (!empty($_GET['success'])): ?>
        <div class="alert success">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php elseif (!empty($_GET['error'])): ?>
        <div class="alert error">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>
</div>

</body>
<?php include 'footer.php'; ?>
</html>



