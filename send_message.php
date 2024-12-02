<?php
session_start();
require 'database_connection.php';

$error_message = '';
$success_message = '';

// Ensure user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Not Logged In</title>
        <link rel='stylesheet' href='styles.css'>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background-color: #f4f4f9;
            }
            .redirect-message-container {
                text-align: center;
                background-color: #ffffff;
                border: 2px solid #e74c3c;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            .redirect-message h2 {
                color: #e74c3c;
                font-size: 1.8rem;
            }
            .redirect-message p {
                margin: 10px 0;
                font-size: 1rem;
                color: #333;
            }
            .redirect-message a {
                color: #3498db;
                text-decoration: none;
                font-weight: bold;
            }
            .redirect-message a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class='redirect-message-container'>
            <div class='redirect-message'>
                <h2>You must be logged in to send a message.</h2>
                <p>Please <a href='login.php'>log in</a> or <a href='signup.php'>sign up</a> to continue.</p>
            </div>
        </div>
    </body>
    </html>";
    exit();
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
