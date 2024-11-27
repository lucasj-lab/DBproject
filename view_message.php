<?php
session_start(); // Ensure the session is started before accessing $_SESSION
require 'database_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to view this message.");
}

$userId = intval($_SESSION['user_id']);
$messageId = intval($_GET['message_id'] ?? 0);

// Validate input
if ($messageId <= 0) {
    die("Invalid message ID.");
}

// Fetch the message details and verify user permissions
$query = "
    SELECT m.Message_ID, m.Subject, m.Message_Text, m.Created_At, 
           u.Name AS Sender_Name, m.Sender_ID, m.Recipient_ID
    FROM messages m
    JOIN user u ON m.Sender_ID = u.User_ID
    WHERE m.Message_ID = ? AND (m.Recipient_ID = ? OR m.Sender_ID = ?)
";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("iii", $messageId, $userId, $userId);
$stmt->execute();
$message = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Check if the message exists and belongs to the logged-in user
if (!$message) {
    die("Message not found or you do not have permission to view this message.");
}

// If the message is unread, mark it as read
if ($message['Recipient_ID'] === $userId) {
    $updateQuery = "UPDATE messages SET Read_Status = 'read' WHERE Message_ID = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $messageId);
    $updateStmt->execute();
    $updateStmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message</title>
    <link rel="stylesheet" href="styles.css">
</head>

    <div class="message-container">
        <h2>Message Details</h2>
        <p><strong>From:</strong> <?php echo htmlspecialchars($message['Sender_Name']); ?></p>
        <p><strong>Subject:</strong> <?php echo htmlspecialchars($message['Subject'] ?: 'No Subject'); ?></p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($message['Created_At']); ?></p>
        <p><strong>Message:</strong></p>
        <p><?php echo nl2br(htmlspecialchars($message['Message_Text'])); ?></p>
        <a href="messages.php" class="btn">Back to Messages</a>
    </div>

    <style>  
.message-container {
    width: 70%;
    max-width: 800px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
h2 {
    color: #062247;
    margin-bottom: 10px;
}
h3 {
    color: #444;
    margin-bottom: 15px;
}
p {
    font-size: 16px;
    color: #555;
    line-height: 1.6;
}
.message-footer {
    margin-top: 20px;
}
.btn {
    display: inline-block;
    padding: 10px 15px;
    background-color: #062247;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
}
.btn:hover {
    background-color: #444;
}

@media (max-width: 768px) { /* Adjust the breakpoint as needed */
    .main-content {
      width: 100%; /* Make it take full width on mobile */
      padding: 10px; /* Reduce padding for smaller screens */
      font-size: 14px; /* Adjust font size */
      box-sizing: border-box; /* Include padding and border in the element's total width and height */
    }
  
    .main-content h2 {
      font-size: 1.5em; /* Adjust heading size */
    }
  
    .main-content label,
    .main-content select {
      display: block; /* Stack form elements vertically */
      width: 100%;
      margin-bottom: 10px;
    }
  
    .main-content table.email-table {
      width: 100%; /* Make table responsive */
      overflow-x: auto; /* Enable horizontal scrolling if needed */
    }
  
    .main-content table.email-table td,
    .main-content table.email-table th {
      padding: 8px; /* Adjust cell padding */
    }
  }
  </style>
</body>
</html>
