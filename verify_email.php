<? 
session_start();
require 'database_connection.php';

if (isset($_GET['token'])) {
    $token = htmlspecialchars($_GET['token']);

    // Check if the token exists
    $stmt = $pdo->prepare("SELECT * FROM user WHERE Verification_Token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Update the user's verification status
        $updateStmt = $pdo->prepare("UPDATE user SET Email_Verified = TRUE, Verification_Token = NULL WHERE Verification_Token = ?");
        if ($updateStmt->execute([$token])) {
            $_SESSION['message'] = "Email verified successfully! You can now log in.";
            $_SESSION['message_type'] = 'success';
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['message'] = "Verification failed. Please try again.";
            $_SESSION['message_type'] = 'error';
        }
    } else {
        $_SESSION['message'] = "Invalid or expired verification link.";
        $_SESSION['message_type'] = 'error';
    }
} else {
    $_SESSION['message'] = "No verification token provided.";
    $_SESSION['message_type'] = 'error';
}

header("Location: signup.php");
exit();
?>