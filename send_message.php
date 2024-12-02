<?php
session_start();
require 'database_connection.php';

// Initialize variables
$error_message = '';
$success_message = '';

// Ensure user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    $error_message = 'You must be logged in to send a message.';
    echo "<p style='color: red;'>$error_message</p>";
    exit;
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listingID = intval($_POST['listing_id'] ?? 0);
    $recipientID = intval($_POST['recipient_id'] ?? 0);
    $messageText = trim($_POST['message_text'] ?? '');
    $subject = trim($_POST['subject'] ?? 'No Subject'); // Default subject if none provided
    $senderID = $user_id; // Use validated user ID from the session

    // Validate inputs
    if (empty($messageText) || !$senderID || !$recipientID) {
        $error_message = 'Message text, sender, and recipient are required.';
    } else {
        try {
            // Check if the recipient exists
            $sql = "SELECT User_ID FROM user WHERE User_ID = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare recipient validation query.");
            }
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
                if (!$stmt) {
                    throw new Exception("Failed to prepare message insertion query.");
                }
                $stmt->bind_param("iiiss", $listingID, $senderID, $recipientID, $subject, $messageText);
                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Message sent successfully!';
                    $_SESSION['message_type'] = 'success';
                    header("Location: messages.php");
                    exit;
                } else {
                    $error_message = 'Failed to send message. Please try again.';
                }
                $stmt->close();
            } else {
                $error_message = 'Recipient does not exist.';
            }
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            $error_message = 'An error occurred. Please try again.';
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
            <p style="color: white; background-color: red; border: 2px solid red; padding: 10px; font-size: xxx-large; font-weight: bold; text-align: center;">
    <?php echo htmlspecialchars($error_message); ?>
</p>
<?php endif; ?>

        <?php if (isset($_SESSION['message'])): ?>
            <p style="color: green;"><?php echo htmlspecialchars($_SESSION['message']); ?></p>
            <?php unset($_SESSION['message']); // Clear after displaying ?>
        <?php endif; ?>
    </div>
</body>
</html>
