<?php
// listings.php - Combined version with header and footer

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'database_connection.php';

// Check if the request is an AJAX request for listings data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetchListings'])) {
    // Query to fetch all listings with user, category, and image data
    $sql = "
    SELECT 
        listings.Listing_ID, listings.Title, listings.Description, listings.Price, listings.Thumbnail_Image,
        listings.Date_Posted, user.Name AS User_Name, category.Category_Name, listings.State, listings.City
    FROM 
        listings
    JOIN 
        user ON listings.User_ID = user.User_ID
    JOIN 
        category ON listings.Category_ID = category.Category_ID
    ORDER BY 
        listings.Date_Posted DESC
    ";

    try {
        // Prepare and execute the query using PDO
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        // Fetch all listings from the database
        $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format the date and prepare the listings array
        if ($listings) {
            foreach ($listings as &$listing) {
                // Handle date formatting
                if (!empty($listing['Date_Posted'])) {
                    $datePosted = new DateTime($listing['Date_Posted']);
                    $listing['Formatted_Date'] = $datePosted->format('l, F jS, Y');
                } else {
                    $listing['Formatted_Date'] = "Date not available";
                }
            }
        } else {
            // If no listings are found
            $listings = ["message" => "No listings available."];
        }

        // Output the listings in JSON format
        header('Content-Type: application/json');
        echo json_encode($listings);
    } catch (PDOException $e) {
        // Handle any PDO exceptions
        error_log("Database error: " . $e->getMessage()); // Log the error for debugging
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

                // Use the correct variable name for the image
                const image = listing.Image_URL || "no_image.png"; // Fallback image

                listingDiv.innerHTML = `
    <img src="${listing.Thumbnail_Image || 'no_image.png'}" alt="Thumbnail Image" class="listing-thumbnail">
    <h3><strong>${listing.Title}</strong></h3>
    <p><strong>Description:</strong> ${listing.Description}</p>
    <p><strong>Price:</strong> $${listing.Price}</p>
    <p><strong>Posted by:</strong> ${listing.User_Name}</p>
    <p><strong>Category:</strong> ${listing.Category_Name}</p>
    <p><strong>Location:</strong> ${listing.City}, ${listing.State}</p>
    <p><strong>Posted On:</strong> ${listing.Formatted_Date}</p>
    <button type="button" class="pill-button"
        onclick="window.location.href='listing_details.php?id=${listing.Listing_ID}'">
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

        <!-- Each listing will have the class "listing-container" -->
        <div id="listings">
            <?php foreach ($listings as $listing): ?>
                <div class="listing-container">
                    <img src="<?= htmlspecialchars($listing['Image_URL'] ?? 'no_image.png'); ?>" alt="Listing Image"
                        class="listing-image">
                    <h3><?= htmlspecialchars($listing['Title']); ?></h3>
                    <p><strong>Price:</strong> $<?= htmlspecialchars($listing['Price']); ?></p>
                    <p><strong>Posted by:</strong> <?= htmlspecialchars($listing['User_Name']); ?></p>
                    <p><strong>Category:</strong> <?= htmlspecialchars($listing['Category_Name']); ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($listing['City']); ?>,
                        <?= htmlspecialchars($listing['State']); ?>
                    </p>
                    <p><strong>Posted on:</strong>
                        <?= htmlspecialchars($listing['Formatted_Date'] ?? "Date not available"); ?></p>
                    <button type="button" class="pill-button"
                        onclick="window.location.href='listing_details.php?id=<?= isset($listing['Listing_ID']) ? htmlspecialchars($listing['Listing_ID']) : 0; ?>'">
                        View Listing
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

</body>