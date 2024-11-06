<?php
// listings.php - Combined version with header and footer

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'database_connection.php';

// Check if the request is an AJAX request for listings data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetchListings'])) {
    // Fetch all listings with user, category, and image data
    $sql = "
        SELECT 
            listings.Listing_ID, listings.Title, listings.Description, listings.Price, listings.Date_Posted, 
            user.Name AS User_Name, category.Category_Name, listings.State, listings.City, images.Image_URL
        FROM 
            listings
        JOIN 
            user ON listings.User_ID = user.User_ID
        JOIN 
            category ON listings.Category_ID = category.Category_ID
        LEFT JOIN 
            images ON listings.Listing_ID = images.Listing_ID
        ORDER BY 
            listings.Date_Posted DESC
    ";
    $result = $conn->query($sql);

    // Prepare listings array
    $listings = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Format the date
            $datePosted = new DateTime($row['Date_Posted']);
            $row['Formatted_Date'] = $datePosted->format('l, F jS, Y');
            $listings[] = $row;
        }
    } else {
        // If no listings are found
        $listings = ["message" => "No listings available."];
    }

    // Output the listings in JSON format
    header('Content-Type: application/json');
    echo json_encode($listings);
    $conn->close();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOMt23cez/3paNdF+K9aIIXUXl09Aq5AxlE9+y5T" crossorigin="anonymous">

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            fetchListings();
        });

        function fetchListings() {
            fetch('listings.php?fetchListings=true')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
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

                const image = listing.Image_URL || "no_image.png"; // Placeholder image
                listingDiv.innerHTML = `
                    <img src="${image}" alt="Listing Image" class="listing-image">
                    <h3><strong>${listing.Title}</strong></h3>
                    <p><strong>Description:</strong> ${listing.Description}</p>
                    <p><strong>Price:</strong> $${listing.Price}</p>
                    <p><strong>Posted by:</strong> ${listing.User_Name}</p>
                    <p><strong>Category:</strong> ${listing.Category_Name}</p>
                    <p><strong>Location:</strong> ${listing.City}, ${listing.State}</p>
                    <p><strong>Date Posted:</strong> ${listing.Formatted_Date}</p>
                    <button type="button" class="pill-button" onclick="window.location.href='listing_details.php?id=${listing.Listing_ID}'">
                        View Listing
                    </button>
                `;

                listingsContainer.appendChild(listingDiv);
            });
        }

        function toggleMobileMenu() {
            document.getElementById("mobileMenu").classList.toggle("active");
        }
    </script>
</head>

<body>
    <?php include 'header.php'; ?>

    <main>
        <section id="listings">
            <p>Loading listings...</p>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <!-- Add styles for responsive design and general layout -->
    <style>
        /* General Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; color: #333; }
        header { background-color: #1a73e8; color: #fff; padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .desktop-menu, .mobile-menu ul { list-style: none; }
        .desktop-menu li a, .mobile-menu ul li a { color: #fff; text-decoration: none; font-weight: bold; }

        /* Responsive Menu */
        .hamburger { display: none; font-size: 1.8rem; color: #fff; cursor: pointer; }
        .mobile-menu { display: none; position: absolute; top: 100%; right: 0; background-color: #1a73e8; padding: 1rem; border-radius: 8px; box-shadow: 0.2rem 0.2rem 0.5rem rgba(0, 0, 0, 0.1); }
        .mobile-menu.active { display: block; width: fit-content; }
        .mobile-menu ul { display: flex; flex-direction: column; }
        @media (max-width: 768px) { .desktop-menu { display: none; } .hamburger { display: block; } }

        /* Listings Section */
        .listing-item { background-color: #fff; padding: 1rem; margin: 1rem 0; border-radius: 8px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); }
        .listing-item h3 { margin: 0.5rem 0; }
    </style>
</body>

</html>
