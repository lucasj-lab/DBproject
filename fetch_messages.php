<?php
require 'database_connection.php';

$senderId = $_GET['sender_id'];
$receiverId = $_GET['receiver_id'];
$listingId = $_GET['listing_id'] ?? null;

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
    WHERE (m.Sender_ID = :sender_id AND m.Receiver_ID = :receiver_id)
       OR (m.Sender_ID = :receiver_id AND m.Receiver_ID = :sender_id)
";

if ($listingId) {
    $sql .= " AND m.Listing_ID = :listing_id";
}

$sql .= " ORDER BY m.Date_Sent ASC";

$stmt = $pdo->prepare($sql);
$params = [
    'sender_id' => $senderId,
    'receiver_id' => $receiverId
];

if ($listingId) {
    $params['listing_id'] = $listingId;
}

$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($messages);
?>
