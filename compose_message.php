<?php
require 'database_connection.php';

// Get the listing and recipient details from the URL
$listingID = $_GET['listing_id'];
$recipientID = $_GET['recipient_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compose Message</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to the CSS file -->
</head>
<body>
<?php include 'header.php'; ?>

    <div class="compose-message-container">
        <h1 class="page-title">Send a Message</h1>
        <form action="send_message.php" method="POST" class="compose-message-form">
            <input type="hidden" name="listing_id" value="<?php echo $listingID; ?>">
            <input type="hidden" name="recipient_id" value="<?php echo $recipientID; ?>">

            <div class="form-group">
                <label for="message_text" class="form-label">Message:</label>
                <textarea name="message_text" id="message_text" class="message-textarea" rows="5" placeholder="Type your message here..." required></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn send-message-btn">Send Message</button>
            </div>
        </form>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
