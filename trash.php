<?php
$trashQuery = "
    SELECT m.Message_ID, m.Message_Text, m.Created_At, m.Has_Attachment,
           u.Name AS Sender_Name 
    FROM messages m
    JOIN user u ON m.Sender_ID = u.User_ID
    WHERE m.Recipient_ID = ? AND m.Deleted_Status = 1
    ORDER BY m.Created_At DESC
";

$stmt = $conn->prepare($trashQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include 'message_table_template.php';
