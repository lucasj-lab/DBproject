<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'database_connection.php';

// Function to fetch all listings from the database
function getAllListings($pdo) {
    $sql = "
        SELECT 
            listings.Listing_ID,
            listings.Title,
            listings.Description,
            listings.Price,
            listings.Date_Posted,
            listings.State,
            listings.City,
            categories.Category_Name,
            users.Name AS User_Name,
            images.Image_URL AS Thumbnail_Image
        FROM listings
        LEFT JOIN categories ON listings.Category_ID = categories.Category_ID
        LEFT JOIN users ON listings.User_ID = users.User_ID
        LEFT JOIN images ON listings.Listing_ID = images.Listing_ID AND images.Is_Thumbnail = 1
        ORDER BY listings.Date_Posted DESC
    ";

    // Debug the SQL query
    error_log("Executing query: $sql");

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Fetch and debug the results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Query Results: " . json_encode($results));
    return $results;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetchListings'])) {
    try {
        $listings = getAllListings($pdo);

        if (empty($listings)) {
            $response = ["message" => "No listings available."];
        } else {
            foreach ($listings as &$listing) {
                $datePosted = $listing['Date_Posted'] ? new DateTime($listing['Date_Posted']) : null;
                $listing['Formatted_Date'] = $datePosted ? $datePosted->format('l, F jS, Y') : "Date not available";
            }
            $response = $listings;
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
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
  
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            fetchListings();
        });

        function fetchListings() {
            fetch('listings.php?fetchListings=true')
                .then(response => response.json())
                .then(data => {
                    console.log("Fetched Listings:", data); // Debug log
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
                    document.getElementById("listings").innerHTML =
                        "<p>Error loading listings. Please try again later.</p>";
                });
        }

        function displayListings(listings) {
            const listingsContainer = document.getElementById("listings");
            listingsContainer.innerHTML = ""; // Clear previous content

            listings.forEach(listing => {
                const listingDiv = document.createElement("div");
                listingDiv.className = "listing-item";

                // Use Thumbnail_Image with a fallback
                const thumbnail = listing.Thumbnail_Image || "uploads/no_image.png";

                listingDiv.innerHTML = `
                    <img src="${thumbnail}" alt="Thumbnail Image" class="listing-thumbnail">
                    <h3>${listing.Title}</h3>
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
