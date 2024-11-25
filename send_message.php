<?php
require 'database_connection.php';
include 'header.php';

// Get the form data
$listingID = $_POST['listing_id'];
$recipientID = $_POST['recipient_id'];
$messageText = $_POST['message_text'];
$senderID = $_SESSION['user_id']; // Assuming the logged-in user's ID is stored in session

// Check if the recipient exists in the user table
$sql = "SELECT User_ID FROM user WHERE User_ID = :recipient_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':recipient_id' => $recipientID]);
$recipientExists = $stmt->fetch();

// If the recipient doesn't exist, show an error and stop the script
if (!$recipientExists) {
    echo "Error: Recipient does not exist.";
    exit; // Stop further execution
}

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

    echo "Message sent successfully!";
    // Redirect to the listing details or messages page
    header("Location: listing_details.php?listing_id=$listingID");
} catch (PDOException $e) {
    die("Error sending message: " . $e->getMessage());
}

include 'footer.php';
?>
