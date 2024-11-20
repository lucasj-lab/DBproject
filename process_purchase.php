<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listingId = $_POST['listingId'] ?? null;

    if ($listingId) {
        // Add purchase processing logic here
        ?>
<div class="popup-overlay">
            <div class="popup-container">
                <div class="popup-header">
                    <h1 class="popup-title">Thank You!</h1>
                </div>
                <div class="popup-body">
                    <p class="popup-message">We appreciate you purchase<br>
<br><strong>Listing ID: 12345</strong>.</p>
                    <p class="popup-message">Your transaction has been successfully processed. <br>
                Enjoy your purchase!</p>
                </div>
                <div class="popup-footer">
                    <button class="close-popup" onclick="closePopup()">Close</button>
                </div>
            </div>
        </div>
        <script>
            function closePopup() {
                document.querySelector('.popup-overlay').style.display = 'none';
            }
        </script>
        <?php
    } else {
        ?>
        <div class="popup-overlay">
            <div class="popup-container">
                <div class="popup-header">
                    <h1 class="popup-title">Error</h1>
                </div>
                <div class="popup-body">
                    <p class="popup-message">Invalid listing. Please try again.</p>
                </div>
                <div class="popup-footer">
                    <button class="close-popup" onclick="closePopup()">Close</button>
                </div>
            </div>
        </div>
        <script>
            function closePopup() {
                document.querySelector('.popup-overlay').style.display = 'none';
            }
        </script>
        <?php
    }
}
?>
