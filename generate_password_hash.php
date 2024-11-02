<?php
// Password to hash
$password = 'Adminadminadmin1';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

echo "Hashed Password: " . $hashedPassword;
?>
