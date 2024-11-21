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
function getAllListings($conn) {
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
                $listing['Formatted_Date'] = $datePosted ? $datePosted->format('l, F jS, Y') : "Date not available";
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
    <style>
        .listings-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .listing-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .listing-item img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }

        .listing-item h3 {
            margin: 10px 0;
        }

        .pill-button {
            padding: 10px 15px;
            border: none;
            border-radius: 20px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }

        .pill-button:hover {
            background-color: #0056b3;

        }


        /* Responsive Layouts */
@media (max-width: 1200px) {
    .listings-container {
        grid-template-columns: repeat(3, 1fr); /* 3 columns for large tablets/small desktops */
    }
}

@media (max-width: 800px) {
    .listings-container {
        grid-template-columns: repeat(2, 1fr); /* 2 columns for tablets/landscape phones */
    }
}

@media (max-width: 500px) {
    .listings-container {
        grid-template-columns: repeat(1, 1fr); /* 1 column for small phones */
    }
}

    </style>
</head>
<body>

<main class="listings">
    <h1>Listings</h1>
    <div id="listings-container" class="listing-container"></div>

    <script>
        // Fetch the listings data
        fetch('listings.php?fetchListings=true')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('listings-container');
                if (data.message) {
                    container.innerHTML = `<p>${data.message}</p>`;
                    return;
                }

                data.forEach(listing => {
                    const listingElement = document.createElement('div');
                    listingElement.className = 'listing-item';

                    const thumbnail = listing.Thumbnail_Image 
                        ? `<img src="${listing.Thumbnail_Image}" alt="${listing.Title}" style="width: 100%; height: auto;">`
                        : '<img src="uploads/default-thumbnail.jpg" alt="No Image Available" style="width: 100%; height: auto;">';

                    listingElement.innerHTML = `
                        ${thumbnail}
                        <div class="listing-info">
                            <h2 class="listing-title">${listing.Title}</h2>
                            <p>${listing.Description}</p>
                            <p><strong>Location:</strong> ${listing.City}, ${listing.State}</p>
                            <p><strong>Category:</strong> ${listing.Category_Name}</p>
                            <p class="listing-price">$${listing.Price}</p>
                        </div>
                    `;

                    container.appendChild(listingElement);
                });
            })
            .catch(error => console.error('Error fetching listings:', error));
    </script>
</main>

</body>
<?php include 'footer.php'; ?>
</html>
