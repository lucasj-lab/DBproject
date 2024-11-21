<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listing Details</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
<?php include 'header.php'; ?>





    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rookielist</title>
    <link rel="stylesheet" href="styles.css">

    <!-- JavaScript -->
    <script>
        // Toggle mobile menu visibility
        function toggleMobileMenu() {
            document.getElementById("mobileMenu").classList.toggle("active");
        }

        // Change user icon border color when logged in
        document.addEventListener("DOMContentLoaded", function () {
            const userIcon = document.getElementById("userIcon");
            const isLoggedIn = true;
            if (isLoggedIn) {
                userIcon.classList.add("logged-in"); // Adds the class for styling if logged in
            }
        });
    </script>

    <style>
        /* CSS to set the user icon border color when logged in */
        .user-icon.logged-in {
            border: 2px solid green;
            /* Modify this style as per your requirements */
        }
    </style>




    <div class="create-listing-container"> <!-- Main container for the listing -->
        <h1 class="edit-listing-title">Listing Details</h1>

        <!-- Image Gallery Section -->
        <div class="image-gallery">
            <img id="mainImage" src="uploads/1731835097_M965VL-CAMO_Camo-Hayden-Tote_1-1.jpg" class="main-image"
                alt="Main Image">
            <div class="thumbnail-container">
                <img src="uploads/1731835097_M851VL-TCAMEL_Camel-Audrey-Purse_1-1.jpg" class="thumbnail-image"
                    onclick="changeMainImage(this.src)" alt="Thumbnail">
                <img src="uploads/1731835097_M965VL-CAMO_Camo-Hayden-Tote_1-1.jpg" class="thumbnail-image"
                    onclick="changeMainImage(this.src)" alt="Thumbnail">
            </div>
        </div>

        <!-- Listing Details Wrapper -->
        <div class="listing-details-wrapper">
            <div class="form-group">
                <label for="title"><strong>Title:</strong></label>
                <p id="title">Tv</p>
            </div>

            <div class="form-group">
                <label for="description"><strong>Description:</strong></label>
                <p id="description">Big</p>
            </div>

            <div class="form-group">
                <label for="price"><strong>Price:</strong></label>
                <p id="price">$87.00</p>
            </div>

            <div class="form-group">
                <label for="state"><strong>State:</strong></label>
                <p id="state">AL</p>
            </div>

            <div class="form-group"> 
                <label for="city"><strong>City:</strong></label>
                <p id="city">Mobile</p>
            </div>
        </div>
        <div style="text-align: center; margin-top: 20px;">
        
               <button id="buyNowBtn" class="btn">Buy Now</button>
                <a href="listings.php" class="btn">All Listings</a>
                <button onclick="history.back()" class="back-button">Go Back</button>
             

             
            </div>


        </div>



        <script>
            // Function to update the main image when a thumbnail is clicked
            function changeMainImage(src) {
                document.getElementById('mainImage').src = src;
            }
        </script>








        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Buy Now Modal</title>
        <style>
            /* Modal Styles */
            .modal {
                display: none;
                /* Hidden by default */
                position: fixed;
                z-index: 1;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0, 0, 0, 0.5);
                /* Black with opacity */
                justify-content: center;
                align-items: center;
            }

            .modal-content {
                background-color: white;
                padding: 20px;
                border-radius: 10px;
                width: 90%;
                max-width: 500px;
                text-align: center;
            }

            .modal-content h2 {
                margin-top: 0;
            }

            .close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
            }

            .close:hover,
            .close:focus {
                color: black;
                text-decoration: none;
            }

            .btn {
                padding: 10px 20px;
                text-decoration: none;
                background-color: #007bff;
                color: white;
                border-radius: 5px;
                display: inline-block;
                margin-top: 15px;
                cursor: pointer;
            }

            .btn:hover {
                background-color: #0056b3;
            }
        </style>


        <!-- Buy Now Button -->


        <!-- Modal -->
        <div id="buyNowModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeModal">×</span>
                <h2>Buy Now</h2>
                <p><strong>Title:</strong> Amazing Product</p>
                <p><strong>Price:</strong> $100</p>
                <p><strong>Description:</strong> This is a fantastic product you will love!</p>
                <form action="process_purchase.php" method="POST">
                    <input type="hidden" name="listingId" value="12345">
                    <button type="submit" class="btn">Confirm Purchase</button>
                </form>
            </div>
        </div>

        <script>
            // JavaScript to Handle Modal
            const modal = document.getElementById('buyNowModal');
            const btn = document.getElementById('buyNowBtn');
            const close = document.getElementById('closeModal');

            // Open Modal
            btn.onclick = function () {
                modal.style.display = "flex";
            };

            // Close Modal
            close.onclick = function () {
                modal.style.display = "none";
            };

            // Close Modal when clicking outside the modal content
            window.onclick = function (event) {
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            };
        </script>




</div>
</body>



<footer>
    <?php include 'footer.php'; ?>
  </footer>
</html>