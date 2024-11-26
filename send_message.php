<?php
require 'database_connection.php';
include 'header.php';


$error_message = '';
$success_message = '';

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listingID = intval($_POST['listing_id'] ?? 0);
    $recipientID = intval($_POST['recipient_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? 'No Subject');
    $messageText = trim($_POST['messages_text'] ?? '');
    $senderID = intval($_SESSION['user_id'] ?? 0); // Assuming the logged-in user's ID is stored in session

    // Validate input fields
    if (!$listingID || !$recipientID || !$messageText || !$senderID) {
        $error_message = 'All fields are required.';
    } else {
        // Check if the recipient exists in the user table
        $sql = "SELECT User_ID FROM user WHERE User_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $recipientID);
        $stmt->execute();
        $recipientExists = $stmt->get_result()->fetch_assoc();

        if (!$recipientExists) {
            $error_message = 'Error: Recipient does not exist.';
        } else {
            try {
                // Insert the message into the database
                $insertSQL = "
                    INSERT INTO messages (Listing_ID, Sender_ID, Recipient_ID, Subject, Message_Text, Created_At)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ";

                $stmt = $conn->prepare($insertSQL);
                $stmt->bind_param("iiiss", $listingID, $senderID, $recipientID, $subject, $messageText);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $success_message = 'Message sent successfully!';
                    // Redirect to the listing details or messages page
                    header("Location: listing_details.php?listing_id=$listingID&message=success");
                    exit;
                } else {
                    $error_message = 'Failed to send message. Please try again.';
                }
            } catch (Exception $e) {
                $error_message = 'Error sending message: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Message</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="send-message-container">
        <h2>Send a Message</h2>

        <!-- Display error message if form validation fails -->
        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <!-- Display success message -->
        <?php if (!empty($success_message)): ?>
            <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <form action="send_message.php" method="POST" class="message-form">
            <div class="form-group">
                <label for="subject">Subject:</label>
                <input 
                    type="text" 
                    name="subject" 
                    id="subject" 
                    placeholder="Enter a subject" 
                    value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" 
                    required>
            </div>
            <div class="form-group">
                <label for="message_text">Message:</label>
                <textarea 
                    name="message_text" 
                    id="message_text" 
                    placeholder="Type your message here..." 
                    rows="5" 
                    required><?php echo htmlspecialchars($_POST['message_text'] ?? ''); ?></textarea>
            </div>
            <input type="hidden" name="listing_id" value="<?php echo htmlspecialchars($_GET['listing_id'] ?? ''); ?>">
            <input type="hidden" name="recipient_id" value="<?php echo htmlspecialchars($_GET['recipient_id'] ?? ''); ?>">
            <div class="form-actions">
                <button type="submit" class="btn">Send Message</button>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
