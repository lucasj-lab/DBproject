<?php
require 'database_connection.php';
require 'listing_queries.php';

// Handle AJAX request for fetching listings
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetchListings'])) {
    try {
        $listings = getAllListings($pdo);

        // Check if listings exist
        if (empty($listings)) {
            $response = ["message" => "No listings available."];
        } else {
            foreach ($listings as &$listing) {
                // Format the date
                if (!empty($listing['Date_Posted'])) {
                    $datePosted = new DateTime($listing['Date_Posted']);
                    $listing['Formatted_Date'] = $datePosted->format('l, F jS, Y');
                } else {
                    $listing['Formatted_Date'] = "Date not available";
                }
            }
            $response = $listings;
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(["error" => "Database error. Please try again later."]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Listings</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
        integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOMt23cez/3paNdF+K9aIIXUXl09Aq5AxlE9+y5T" crossorigin="anonymous">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            fetchListings();
        });

        function fetchListings() {
            fetch('listings.php?fetchListings=true')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        document.getElementById("listings").innerHTML = `<p>${data.error}</p>`;
                    } else if (data.message) {
                        document.getElementById("listings").innerHTML = `<p>${data.message}</p>`;
                    } else {
                        displayListings(data);
                    }
                })
                .catch(error => {
                    console.error('Error fetching listings:', error);
                    document.getElementById("listings").innerHTML = "<p>Error loading listings. Please try again later.</p>";
                });
        }

        function displayListings(listings) {
            const listingsContainer = document.getElementById("listings");
            listingsContainer.innerHTML = ""; // Clear previous content

            listings.forEach(listing => {
                const listingDiv = document.createElement("div");
                listingDiv.className = "listing-item";

                // Use fallback for missing thumbnail
                const thumbnail = listing.Thumbnail_Image || "no_image.png";

                listingDiv.innerHTML = `
                    <img src="${thumbnail}" alt="Thumbnail Image" class="listing-thumbnail">
                    <h3><strong>${listing.Title}</strong></h3>
                    <p><strong>Description:</strong> ${listing.Description}</p>
                    <p><strong>Price:</strong> $${listing.Price}</p>
                    <p><strong>Posted by:</strong> ${listing.User_Name}</p>
                    <p><strong>Category:</strong> ${listing.Category_Name}</p>
                    <p><strong>Location:</strong> ${listing.City}, ${listing.State}</p>
                    <p><strong>Posted On:</strong> ${listing.Formatted_Date}</p>
                    <button type="button" class="pill-button"
                        onclick="window.location.href='listing_details.php?listing_id=${listing.Listing_ID}'">
                        View Listing
                    </button>
                `;
                listingsContainer.appendChild(listingDiv);
            });
        }
    </script>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="listings-container">
        <h2>Active Listings</h2>
        <div id="listings">
            <!-- Listings will be dynamically populated here -->
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
