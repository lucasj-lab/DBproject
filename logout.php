<?php
session_start(); // Start the session

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to the home page
header("Location: index.php");
exit();
<?>

<!-- Logout Button -->
<a href="#" class="pill-button logout-button" onclick="showLogoutModal()">Log Out</a>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <h2>Confirm Logout</h2>
        <p>Are you sure you want to log out?</p>
        <div class="modal-buttons">
            <a href="logout.php" class="pill-button confirm-logout">Yes, Log Out</a>
            <button class="pill-button cancel-logout" onclick="hideLogoutModal()">Cancel</button>
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