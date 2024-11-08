<?php
require 'database_connection.php';

// Fetch listings with category, user, and image data
$sql = "
    SELECT 
        listings.Listing_ID, listings.Title, listings.Description, listings.Price, listings.Date_Posted, 
        user.Name AS User_Name, category.Category_Name, listings.State, listings.City, images.Image_URL 
    FROM listings
    JOIN user ON listings.User_ID = user.User_ID
    JOIN category ON listings.Category_ID = category.Category_ID
    LEFT JOIN images ON listings.Listing_ID = images.Listing_ID
    ORDER BY listings.Date_Posted DESC
";
$result = $conn->query($sql);
$listings = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Group listings with multiple images under the same Listing_ID
        $listings[$row['Listing_ID']]['details'] = $row;
        $listings[$row['Listing_ID']]['images'][] = $row['Image_URL'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Listings</title>
</head>
<body>
<header>
    <?php include 'header.php'; ?>
</header>
<div id="listings"></div> <!-- This container will hold all listings -->
<footer>
    <?php include 'footer.php'; ?>
</footer>
<script>
// Pass PHP listings data to JavaScript
const listings = <?php echo json_encode($listings); ?>;

// JavaScript function to display listings
function displayListings(listings) {
    const listingsContainer = document.getElementById("listings");
    listingsContainer.innerHTML = "";  // Clear previous content

    for (const listingID in listings) {
        const listing = listings[listingID].details;
        const images = listings[listingID].images;

        // Create listing HTML structure
        const listingDiv = document.createElement("div");
        listingDiv.className = "listing-item";

        // Fallback to a placeholder image if no images are available
        const image = images && images[0] ? `/${images[0]}` : "/images/no_image.png";

        // Set the HTML content of the listing div
        listingDiv.innerHTML = `
            <img src="${image}" alt="Listing Image" class="listing-image" style="width:100px;height:auto;">
            <h3>${listing.Title}</h3>
            <p>Description: ${listing.Description}</p>
            <p>Price: $${listing.Price}</p>
            <p>Category: ${listing.Category_Name}</p>
            <p>Posted by: ${listing.User_Name}</p>
            <p>Location: ${listing.City}, ${listing.State}</p>
            <p>Posted on: ${new Date(listing.Date_Posted).toLocaleDateString()}</p>
            <button type="button" class="pill-button" onclick="window.location.href='listing_details.php?id=${listing.Listing_ID}'">
                View Listing
            </button>
            <div class="images">
                ${images.map(img => `<img src="${img}" alt="Listing Image" style="width:100px;height:auto;">`).join('')}
            </div>
        `;

        listingsContainer.appendChild(listingDiv);
    }
}

// Display listings on page load
document.addEventListener("DOMContentLoaded", () => displayListings(listings));
</script>

</body>
</html>











