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
                    <p class="popup-message">We appreciate your purchase<br>
                        <br><strong>Listing ID: <?php echo htmlspecialchars($listingId); ?></strong>.
                    </p>
                    <p class="popup-message">Your transaction has been successfully processed. <br>
                        Enjoy!</p>
                </div>
                <div class="popup-footer">
                    <button class="close-popup" onclick="closePopup()">Close</button>
                </div>
            </div>
        </div>

        <script>
            function closePopup() {
                document.querySelector('.popup-overlay').style.display = 'none';
                // Redirect to home page
                window.location.href = 'index.php'; // Replace 'index.php' with your actual home page URL
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
                // Redirect to home page
                window.location.href = 'index.php'; // Replace 'index.php' with your actual home page URL
            }
        </script>
        <?php
    }
}
?>

<style>
    /* Overlay Styling */
    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #007bff1c;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    /* Popup Container */
    .popup-container {
        background: #ffffff;
        border-radius: 10px;
        width: 90%;
        max-width: 400px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        text-align: center;
        overflow: hidden;
        animation: slideIn 0.3s ease-out;
    }

    /* Popup Header */
    .popup-header {
        background: #28a745;
        color: white;
        padding: 20px;
    }

    .popup-title {
        margin: 0;
        font-size: 24px;
    }

    /* Popup Body */
    .popup-body {
        padding: 20px;
    }

    .popup-message {
        font-size: 18px;
        color: #333;
        margin: 10px 0;
        line-height: 1.5;
    }

    /* Popup Footer */
    .popup-footer {
        padding: 15px;
        background: #f8f9fa;
        text-align: center;
    }

    .close-popup {
        background: #007bff;
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .close-popup:hover {
        background: #0056b3;
    }
</style>
