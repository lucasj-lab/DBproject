<?php
require 'database_connection.php';
require 'listing_queries.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Listings</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include 'header.php'; ?>

<main>
    <section id="listings">
        <p>Loading listings...</p>
    </section>
</main>

<script>
    // Fetch listings when the page loads
    document.addEventListener("DOMContentLoaded", fetchListings);

    function fetchListings() {
        fetch('listings.php')
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
                    onclick="window.location.href='listing_details.php?listing_listing_id=${listing.Listing_ID}'">
                    View Listing
                </button>
            `;

            listingsContainer.appendChild(listingDiv);
        });
    }
</script>

<?php include 'footer.php'; ?>

</body>
</html>
