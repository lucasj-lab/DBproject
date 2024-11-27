<?php
session_start(); // Ensure session is started before any output
require 'database_connection.php';
include 'header.php';

$error_message = '';
$success_message = '';

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listingID = intval($_POST['listing_id'] ?? 0);
    $recipientID = intval($_POST['recipient_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? ''); // Allow empty subject
    $messageText = trim($_POST['message_text'] ?? '');
    $senderID = intval($_SESSION['user_id'] ?? 0);

    // Use default subject if none provided
    if ($subject === '') {
        $subject = 'No Subject';
    }

    // Validate input fields
    if (!$messageText || !$senderID || !$recipientID) {
        $error_message = 'Message text, sender, and recipient are required.';
    } else {
        // Check if the recipient exists
        $sql = "SELECT User_ID FROM user WHERE User_ID = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error_message = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("i", $recipientID);
            $stmt->execute();
            $recipientExists = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$recipientExists) {
                $error_message = 'Error: Recipient does not exist.';
            } else {
                // Insert the message into the database
                $insertSQL = "
                    INSERT INTO messages (Listing_ID, Sender_ID, Recipient_ID, Subject, Message_Text, Created_At)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ";
                $stmt = $conn->prepare($insertSQL);

                if (!$stmt) {
                    $error_message = "Failed to prepare statement: " . $conn->error;
                } else {
                    $stmt->bind_param("iiiss", $listingID, $senderID, $recipientID, $subject, $messageText);
                    if ($stmt->execute()) {
                        $success_message = 'Message sent successfully!';
                        header("Location: messages.php?status=success");
                        exit;
                    } else {
                        $error_message = 'Failed to send message. Please try again.';
                    }
                    $stmt->close();
                }
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

        <!-- Display error message -->
        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <!-- Display success message -->
        <?php if (!empty($success_message)): ?>
            <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>


            <div class="form-group">
                <label for="message_text">Message:</label>
                <textarea 
                    name="message_text" 
                    id="message_text" 
                    placeholder="Type your message here..." 
                    rows="5" 
                    required><?php echo htmlspecialchars($_POST['message_text'] ?? ''); ?></textarea>
            </div>
            <input type="hidden" name="listing_id" value="<?php echo htmlspecialchars($_POST['listing_id'] ?? 0); ?>">
            <input type="hidden" name="recipient_id" value="<?php echo htmlspecialchars($_POST['recipient_id'] ?? 0); ?>">
            <div class="form-actions">
                <button type="submit" class="btn">Send Message</button>
            </div>
        </form>
    </div>
</body>
</html>
