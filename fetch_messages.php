<?php
require 'database_connection.php'; // Ensure $conn is a valid MySQLi connection

$senderId = intval($_GET['sender_id'] ?? 0);
$receiverId = intval($_GET['receiver_id'] ?? 0);
$listingId = intval($_GET['listing_id'] ?? 0);

// Validate input
if (!$senderId || !$receiverId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid sender or receiver ID']);
    exit;
}

// Build the SQL query
$sql = "
    SELECT 
        m.Message_ID,
        m.Message_Text,
        m.Date_Sent,
        m.Sender_ID,
        m.Receiver_ID,
        u1.username AS Sender_Username,
        u2.username AS Receiver_Username
    FROM message m
    JOIN user u1 ON m.Sender_ID = u1.user_id
    JOIN user u2 ON m.Receiver_ID = u2.user_id
    WHERE (m.Sender_ID = ? AND m.Receiver_ID = ?)
       OR (m.Sender_ID = ? AND m.Receiver_ID = ?)
";

// Add condition for listing ID if provided
if ($listingId) {
    $sql .= " AND m.Listing_ID = ?";
}

// Order by date sent
$sql .= " ORDER BY m.Date_Sent ASC";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameters dynamically based on whether listingId is provided
if ($listingId) {
    $stmt->bind_param("iiiii", $senderId, $receiverId, $receiverId, $senderId, $listingId);
} else {
    $stmt->bind_param("iiii", $senderId, $receiverId, $receiverId, $senderId);
}

// Execute the query
$stmt->execute();

// Fetch results
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);

// Output results as JSON
header('Content-Type: application/json');
echo json_encode($messages);
?>
