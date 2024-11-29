<?php
$draftsQuery = "
    SELECT m.Message_ID, m.Message_Text, m.Created_At, m.Has_Attachment
    FROM messages m
    WHERE m.Sender_ID = ? AND m.Status = 'draft'
    ORDER BY m.Created_At DESC
";

$stmt = $conn->prepare($draftsQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include 'message_table_template.php';
