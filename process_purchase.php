<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listingId = $_POST['listingId'] ?? null;

    if ($listingId) {
        // Add purchase processing logic here
        // Example: Save purchase details to the database or initiate payment
        ?>
        <div class="success-message">
            <h1>Thank you for purchasing listing ID: <?php echo htmlspecialchars($listingId); ?></h1>
        </div>
        <?php
    } else {
        ?>
        <div class="success-message">
            <h1>Invalid listing. Please try again.</h1>
        </div>
        <?php
    }
}
?>
