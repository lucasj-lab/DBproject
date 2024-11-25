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
    display: inline-block;
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    text-align: center;
}
.btn:hover {
    background-color: #0056b3;
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

<body>
    <?php include 'header.php'; ?>

    <main class="dope-dashboard">
        <h1 class="dashboard-title">Welcome, <?php echo htmlspecialchars($user['Name']); ?></h1>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
        <p><strong>Member Since:</strong>
            <?php echo htmlspecialchars((new DateTime($user['Date_Joined']))->format('F j, Y')); ?>
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
                                            ? (new DateTime($listing['Date_Posted']))->format('F j, Y')
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
            <div>
    <button class="btn" onclick="window.location.href='create_listing.php'">New Listing</button>
    <button class="btn" onclick="window.location.href='messages.php'">Messages</button>
</div>

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
            .catch(error => console.error('Error:', error));
    }
};

    </script>
</body>
<?php include 'footer.php'; ?>
</html>
