<?php
session_start(); // Start the session

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to the home page
header("Location: index.php");
?>

<!DOCTYPE html>
<lang="en">
<body>
    <?php include 'header.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listings</title>
 
</head>
<body></body>

<!-- Logout Button -->
<a href="#" class="pill-button logout-button" onclick="showLogoutModal()">Log Out</a>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <h2>Confirm Logout</h2>
        <p>Are you sure you want to log out?</p>
        <div class="modal-actions">
            <button id="confirmLogout" class="btn btn-danger">Logout</button>
            <button id="cancelLogout" class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</div>


<script>

// Show the logout confirmation modal
function showLogoutModal() {
    document.getElementById('logoutModal').style.display = 'flex';
}

// Hide the logout confirmation modal
function hideLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
}



</script>

</body>

<footer>
    <?php include 'footer.php'; ?>
  </footer>

  </html>