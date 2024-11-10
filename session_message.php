<?php
session_start();
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    echo '<div class="session-message">You are logged in</div>';
}
