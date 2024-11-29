<table class="message-table">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectAllCheckbox" title="Select All"></th>
            <th>Sender</th>
            <th>Message</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($messages as $message): ?>
        <tr>
            <td><input type="checkbox" class="messageCheckbox" value="<?= $message['Message_ID'] ?>"></td>
            <td><?= htmlspecialchars($message['Sender_Name'] ?? $message['Recipient_Name']) ?></td>
            <td><?= htmlspecialchars(substr($message['Message_Text'], 0, 50)) ?>...</td>
            <td><?= date('M d, Y', strtotime($message['Created_At'])) ?></td>
            <td>
                <a href="delete_message.php?message_id=<?= $message['Message_ID'] ?>">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
