<?php

require 'database_connection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Function to fetch all listings
 * 
 * @param mysqli $conn The database connection object
 * @return array The array of listings
 * @throws Exception If there is a database or query error
 */
function getAllListings($conn)
{
    if (!$conn) {
        throw new Exception("Invalid database connection.");
    }

    // Define the SQL query
    $sql = "
    SELECT 
        listings.Listing_ID,
        listings.Title,
        listings.Description,
        listings.Price,
        listings.Date_Posted,
        listings.State,
        listings.City,
        category.Category_Name,
        user.Name AS User_Name,
        images.Image_URL AS Thumbnail_Image
    FROM listings
    LEFT JOIN category ON listings.Category_ID = category.Category_ID
    LEFT JOIN user ON listings.User_ID = user.User_ID
    LEFT JOIN images ON listings.Listing_ID = images.Listing_ID AND images.Is_Thumbnail = 1
    ORDER BY listings.Date_Posted DESC
    ";

    error_log("Executing SQL Query: $sql");

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL preparation failed: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Query execution failed: " . $conn->error);
    }

    // Fetch the data
    $listings = [];
    while ($row = $result->fetch_assoc()) {
        $listings[] = $row;
    }

    $stmt->close(); // Clean up the statement
    return $listings;
}

// Handle GET request for fetching listings
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetchListings'])) {
    try {
        $listings = getAllListings($conn);

        if (empty($listings)) {
            error_log("No listings found");
            $response = ["message" => "No listings available."];
        } else {
            foreach ($listings as &$listing) {
                $datePosted = $listing['Date_Posted'] ? new DateTime($listing['Date_Posted']) : null;
                $listing['Formatted_Date'] = $datePosted ? $datePosted->format('F j, Y') : "Date not available";
            }
            $response = $listings;
        }

        header('Content-Type: application/json');
        echo json_encode($response);

    } catch (Exception $e) {
        error_log("Error fetching listings: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(["error" => "Error fetching listings."]);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'header.php'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listings</title>
</head>

<body>

    <main class="listings">
        <h1>Listings</h1>
        <div id="listings-container" class="listing-container"></div>

        <script>
    // Function to sanitize dynamic content
    function sanitizeHTML(str) {
        const tempDiv = document.createElement('div');
        tempDiv.textContent = str;
        return tempDiv.innerHTML;
    }

    // Fetch the listings data
    fetch('listings.php?fetchListings=true')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('listings-container');
            if (data.message) {
                container.innerHTML = `<p>${sanitizeHTML(data.message)}</p>`;
                return;
            }

            data.forEach(listing => {
                const listingElement = document.createElement('div');
                listingElement.className = 'listing-item';

                const thumbnail = listing.Thumbnail_Image
                    ? `<img src="${sanitizeHTML(listing.Thumbnail_Image)}" alt="${sanitizeHTML(listing.Title)}" class="listing-image">`
                    : `<img src="uploads/default-thumbnail.jpg" alt="No Image Available" class="listing-image">`;

                // Updated: Removed hidden details and toggle button
                listingElement.innerHTML = `
                    <div>
                        ${thumbnail}
                        <h3>${sanitizeHTML(listing.Title)}</h3>
                        <p class="listing-price">$${sanitizeHTML(new Intl.NumberFormat("en-US", {
                            style: "decimal",
                            minimumFractionDigits: 2,
                        }).format(listing.Price))}</p>
                        
                        <!-- Updated button behavior -->
                        <button type="button" class="pill-button"
                            onclick="window.location.href='listing_details.php?listing_id=${sanitizeHTML(listing.Listing_ID.toString())}'">
                            View Listing
                        </button>
                    </div>
                `;

                container.appendChild(listingElement);
            });
        })
        .catch(error => {
            console.error('Error fetching listings:', error);
            const container = document.getElementById('listings-container');
            container.innerHTML = `<p>Unable to load listings. Please try again later.</p>`;
        });
</script>

    </main>

</body>
<?php include 'footer.php'; ?>

</html>