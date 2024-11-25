<?php
require 'connection_database.php';

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
</head>
<body>
    <h1>Send a Message</h1>
    <form action="send_message.php" method="POST">
        <input type="hidden" name="listing_id" value="<?php echo $listingID; ?>">
        <input type="hidden" name="recipient_id" value="<?php echo $recipientID; ?>">
        <label for="message_text">Message:</label><br>
        <textarea name="message_text" id="message_text" rows="5" required></textarea><br><br>
        <button type="submit">Send Message</button>
    </form>
</body>
</html>
