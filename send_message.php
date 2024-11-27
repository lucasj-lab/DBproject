<?php
session_start();
require 'database_connection.php';

$user_id = $_SESSION['user_id'];
if ($stmt->execute()) {
    $_SESSION['message'] = 'Message sent successfully!';
    $_SESSION['message_type'] = 'success';
    header("Location: messages.php");
    exit;
}

// Initialize variables
$error_message = '';
$success_message = '';

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listingID = intval($_POST['listing_id'] ?? 0);
    $recipientID = intval($_POST['recipient_id'] ?? 0);
    $messageText = trim($_POST['message_text'] ?? '');
    $subject = trim($_POST['subject'] ?? ''); // Accept optional subject
    $senderID = intval($_SESSION['user_id'] ?? 0);

    // Default subject if none provided
    if ($subject === '') {
        $subject = 'No Subject';
    }

    if (!$messageText || !$senderID || !$recipientID) {
        $error_message = 'Message text, sender, and recipient are required.';
    } else {
        // Check if the recipient exists
        $sql = "SELECT User_ID FROM user WHERE User_ID = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $recipientID);
            $stmt->execute();
            $recipientExists = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($recipientExists) {
                // Insert the message into the database
                $insertSQL = "
                    INSERT INTO messages (Listing_ID, Sender_ID, Recipient_ID, Subject, Message_Text, Created_At)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ";
                $stmt = $conn->prepare($insertSQL);
                if ($stmt) {
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
            } else {
                $error_message = 'Recipient does not exist.';
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
    <div>
        <h1>Send Message</h1>
        <?php if ($error_message): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <p style="color: green;"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
