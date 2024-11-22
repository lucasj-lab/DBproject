<?php
if (isset($_SESSION['message']) && isset($_SESSION['message_type'])): ?>
    <div class="session-message <?php echo htmlspecialchars($_SESSION['message_type']); ?>">
        <?php echo htmlspecialchars($_SESSION['message']); ?>
    </div>
    <?php
    // Clear the message after displaying
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
endif;
?>
