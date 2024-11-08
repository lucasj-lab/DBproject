<!-- display_listings.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Listings</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
</head>

<body>
    
<header>
<?php include 'header.php'; ?>
</header>


    <main>
        <section id="listings">
            <p>Loading listings...</p>
        </section>
    </main>

    
<footer>
    <?php include 'footer.php'; ?>
</footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            fetchListings();
        });

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
    listingsContainer.innerHTML = "";  // Clear previous content

    listings.forEach(listing => {
        const listingDiv = document.createElement("div");
        listingDiv.className = "listing-item";

        // Prepend the server root to Image_URL and set a placeholder if missing
        const image = listing.Image_URL ? `/${listing.Image_URL}` : "/images/no_image.png";
        
        listingDiv.innerHTML = `
            <img src="${image}" alt="Listing Image" class="listing-image">
            <h3>${listing.Title}</h3>
            <p>Price: $${listing.Price}</p>
            <p>Posted by: ${listing.User_Name}</p>
            <p>Category: ${listing.Category_Name}</p>
            <p>Location: ${listing.City}, ${listing.State}</p>
            <p>Posted on: ${listing.Formatted_Date}</p>
            <button type="button" class="pill-button" onclick="window.location.href='listing_details.php?id=${listing.Listing_ID}'">
                View Listing
            </button>
        `;

        listingsContainer.appendChild(listingDiv);
    });
}

        
    </script>
</body>

</html>