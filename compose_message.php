<?php
require 'database_connection.php';
include 'header.php';



// Get the listing and recipient details from the URL
$listingID = intval($_GET['listing_id'] ?? 0);
$recipientID = intval($_GET['recipient_id'] ?? 0);

// Fetch listing and recipient details for display
$listing = [];
$recipient = [];

// Fetch listing details
if ($listingID) {
    $sql = "SELECT Title FROM listings WHERE Listing_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $listingID);
    $stmt->execute();
    $result = $stmt->get_result();
    $listing = $result->fetch_assoc();
    $stmt->close();
}

// Fetch recipient details
if ($recipientID) {
    $sql = "SELECT Name FROM user WHERE User_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $recipientID);
    $stmt->execute();
    $result = $stmt->get_result();
    $recipient = $result->fetch_assoc();
    $stmt->close();
}

// Handle missing data
if (!$listingID || !$recipientID || !$listing || !$recipient) {
    die("Invalid listing or recipient details provided.");
}
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
    <div class="compose-message-container">
        <h1 class="page-title">Send a Message</h1>

        <p><strong>Listing:</strong> <?php echo htmlspecialchars($listing['Title'] ?? 'Unknown Listing'); ?></p>
        <p><strong>To:</strong> <?php echo htmlspecialchars($recipient['Name'] ?? 'Unknown Recipient'); ?></p>

        <form action="send_message.php" method="POST" class="compose-message-form">
            <input type="hidden" name="listing_id" value="<?php echo $listingID; ?>">
            <input type="hidden" name="recipient_id" value="<?php echo $recipientID; ?>">

            <div class="form-group">
                <label for="subject" class="form-label">Subject:</label>
                <input type="text" name="subject" id="subject" class="form-input" placeholder="Enter a subject" required>
            </div>

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
