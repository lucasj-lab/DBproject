<?php
session_start(); // Start the session

// Check if the logout is confirmed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmLogout'])) {
    // Unset all session variables
    $_SESSION = [];

    // Destroy the session
    session_destroy();

    // Redirect to the home page
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Logout</title>
    <link rel="stylesheet" href="styles.css">
    <style>
    .modal {
    display: flex; /* Visible by default for demonstration */
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    width: 300px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.modal-actions {
    display: flex;
    justify-content: space-around;
    margin-top: 20px;
}

.pill-button {
    padding: 10px 20px;
    border-radius: 20px;
    text-decoration: none;
    cursor: pointer;
}

.btn-danger {
    background-color: #ff4d4d;
    color: #fff;
    border: none;
}

.btn-secondary {
    background-color: #ddd;
    color: #000;
    border: none;
}

.pill-button:hover {
    opacity: 0.8;
}
</style>
</head>

<body>
    <?php include 'header.php'; ?>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="modal" style="display: flex;">
        <div class="modal-content">
            <h2>Confirm Logout</h2>
            <p>Are you sure you want to log out?</p>
            <form method="POST">
                <div class="modal-actions">
                    <button type="submit" name="confirmLogout" class="pill-button btn-danger">Logout</button>
                    <a href="javascript:void(0)" onclick="hideLogoutModal()" class="pill-button btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Hide the logout modal and go back to the previous page
        function hideLogoutModal() {
            window.history.back(); // Go back to the previous page
        }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
