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
    $messageText = trim($_POST['message_text'] ?? ''); // Fixed the field name
    $senderID = intval($_SESSION['user_id'] ?? 0);

    // Validate input fields
    if (!$messageText || !$senderID) {
        $error_message = 'Message text and logged-in user are required.';
    } else {
        // Check if the recipient exists in the user table
        $sql = "SELECT User_ID FROM user WHERE User_ID = ?";
        $stmt = $conn->prepare($sql);
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
            $stmt->bind_param("iiiss", $listingID, $senderID, $recipientID, $subject, $messageText);

            if ($stmt->execute()) {
                // Check if the listing has a thumbnail and link it in the success message
                $thumbnailQuery = "
                    SELECT Image_URL 
                    FROM images 
                    WHERE Listing_ID = ? AND Is_Thumbnail = 1 
                    LIMIT 1
                ";
                $thumbStmt = $conn->prepare($thumbnailQuery);
                $thumbStmt->bind_param("i", $listingID);
                $thumbStmt->execute();
                $thumbnail = $thumbStmt->get_result()->fetch_assoc();
                $thumbStmt->close();

                $thumbnailMessage = '';
                if (!empty($thumbnail['Image_URL'])) {
                    $thumbnailMessage = "<img src='" . htmlspecialchars($thumbnail['Image_URL']) . "' alt='Thumbnail' class='thumbnail-preview'>";
                }

                $success_message = "Message sent successfully! $thumbnailMessage";
                header("Location: messages.php?status=success");
                exit;
            } else {
                $error_message = 'Failed to send message. Please try again.';
            }

            $stmt->close();
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
    <style>
        .thumbnail-preview {
            width: 100px;
            height: 100px;
            margin-top: 10px;
            border-radius: 5px;
        }
    </style>
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
