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

        main.listings {
    width: 90%;
    max-width: 1000px;
    margin: auto;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
}

/* Listing Container */
.listing-container {
    display: grid;
    gap: 10px; /* Space between listing items */
    width: 100%;
}

/* Default: 4x4 Layout */
.listing-container {
    grid-template-columns: repeat(4, 1fr);
}

/* 3x3 Layout for medium screens */
@media (max-width: 768px) {
    .listing-container {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* 2x2 Layout for smaller screens */
@media (max-width: 576px) {
    .listing-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* 1x1 Layout for extra small screens */
@media (max-width: 400px) {
    .listing-container {
        grid-template-columns: repeat(1, 1fr);
    }
}

.listing-item {
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #007bff;
    color: white;
    font-size: 1.2rem;
    border-radius: 5px;
    aspect-ratio: 1 / 1; /* Ensures items are perfect squares */
    text-align: center;
    overflow: hidden; /* Ensures content doesn't overflow */
}
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<main>
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

</main>
</body>
<?php include 'footer.php'; ?>
</html>