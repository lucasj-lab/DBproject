<?php
$sentQuery = "
    SELECT m.Message_ID, m.Message_Text, m.Created_At, m.Has_Attachment,
           u.Name AS Recipient_Name 
    FROM messages m
    JOIN user u ON m.Recipient_ID = u.User_ID
    WHERE m.Sender_ID = ? AND m.Deleted_Status = 0
    ORDER BY m.Created_At DESC
";

$stmt = $conn->prepare($sentQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include 'message_table_template.php';
