<?php
require 'database_connection.php';
include 'header.php';

$error_message = '';
$success_message = '';

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listingID = $_POST['listing_id'] ?? null;
    $recipientID = $_POST['recipient_id'] ?? null;
    $messageText = $_POST['message_text'] ?? null;
    $senderID = $_SESSION['user_id'] ?? null; // Assuming the logged-in user's ID is stored in session

    if (!$listingID || !$recipientID || !$messageText) {
        $error_message = 'All fields are required.';
    } else {
        // Check if the recipient exists in the user table
        $sql = "SELECT User_ID FROM user WHERE User_ID = :recipient_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':recipient_id' => $recipientID]);
        $recipientExists = $stmt->fetch();

        if (!$recipientExists) {
            $error_message = 'Error: Recipient does not exist.';
        } else {
            try {
                // Insert the message into the database
                $insertSQL = "
                    INSERT INTO messages (Listing_ID, Sender_ID, Recipient_ID, Message_Text)
                    VALUES (:listing_id, :sender_id, :recipient_id, :message_text)
                ";

                $stmt = $pdo->prepare($insertSQL);
                $stmt->execute([
                    ':listing_id' => $listingID,
                    ':sender_id' => $senderID,
                    ':recipient_id' => $recipientID,
                    ':message_text' => $messageText,
                ]);

                $success_message = 'Message sent successfully!';
                // Redirect to the listing details or messages page
                header("Location: listing_details.php?listing_id=$listingID");
                exit;
            } catch (PDOException $e) {
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
            <div class="message-fields">
                <textarea 
                    name="message_text" 
                    placeholder="Type your message here..." 
                    required><?php echo htmlspecialchars($messageText ?? ''); ?></textarea>
                <input type="hidden" name="listing_id" value="<?php echo htmlspecialchars($_GET['listing_id'] ?? ''); ?>">
                <input type="hidden" name="recipient_id" value="<?php echo htmlspecialchars($_GET['recipient_id'] ?? ''); ?>">
                <button type="submit" class="btn">Send Message</button>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
