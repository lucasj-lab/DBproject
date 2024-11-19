<?php

require 'database_connection.php';

// Verify connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Function to fetch all listings
function getAllListings($conn) {
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
            `user`.Name AS User_Name,
            images.Image_URL AS Thumbnail_Image
        FROM listings
        LEFT JOIN category ON listings.Category_ID = category.Category_ID
        LEFT JOIN `user` ON listings.User_ID = `user`.User_ID
        LEFT JOIN images ON listings.Listing_ID = images.Listing_ID AND images.Is_Thumbnail = 1
        ORDER BY listings.Date_Posted DESC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL preparation failed: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $listings = [];
    while ($row = $result->fetch_assoc()) {
        // Add full URL for images if needed
        $row['Thumbnail_Image'] = $row['Thumbnail_Image'] 
            ? "http://3.146.237.94/uploads/" . $row['Thumbnail_Image']
            : null;
        $listings[] = $row;
    }

    return $listings;
}

// Check if API is being accessed
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetchListings'])) {
    try {
        $listings = getAllListings($conn);

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
    } catch (Exception $e) {
        error_log("Error fetching listings: " . $e->getMessage());
        echo json_encode(["error" => "Error fetching listings."]);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listings</title>
    <style>
        .listing {
            border: 1px solid #ddd;
            margin: 10px;
            padding: 10px;
            border-radius: 5px;
            display: flex;
            align-items: flex-start;
        }

        .listing img {
            max-width: 150px;
            max-height: 150px;
            margin-right: 20px;
            border-radius: 5px;
        }

        .listing-info {
            flex: 1;
        }

        .listing-title {
            font-size: 1.5em;
            margin: 0;
        }

        .listing-description {
            margin: 10px 0;
        }

        .listing-price {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Listings</h1>
    <div id="listings-container"></div>

    <script>
        // Fetch the listings data
        fetch('http://3.146.237.94/listings.php?fetchListings=true')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('listings-container');
                if (data.message) {
                    container.innerHTML = `<p>${data.message}</p>`;
                    return;
                }
                data.forEach(listing => {
                    const listingElement = document.createElement('div');
                    listingElement.className = 'listing';

                    const thumbnail = listing.Thumbnail_Image 
                        ? `<img src="${listing.Thumbnail_Image}" alt="${listing.Title}">`
                        : '<img src="http://3.146.237.94/uploads/default-thumbnail.jpg" alt="No Image Available">';

                    listingElement.innerHTML = `
                        ${thumbnail}
                        <div class="listing-info">
                            <h2 class="listing-title">${listing.Title}</h2>
                            <p class="listing-description">${listing.Description}</p>
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
</body>
</html>
