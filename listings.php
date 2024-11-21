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
        l.Listing_ID, 
        l.Title, 
        l.Description, 
        l.Price, 
        l.Date_Posted, 
        l.State, 
        l.City, 
        c.Category_Name, 
        u.Name AS User_Name, 
        i.Image_URL AS Thumbnail_Image
    FROM 
        listings l
    JOIN 
        user u ON l.User_ID = u.User_ID
    JOIN 
        category c ON l.Category_ID = c.Category_ID
    LEFT JOIN 
        images i ON l.Listing_ID = i.Listing_ID AND i.Is_Thumbnail = 1
    WHERE 
        l.Title LIKE CONCAT('%', ?, '%') OR 
        l.Description LIKE CONCAT('%', ?, '%') OR 
        c.Category_Name LIKE CONCAT('%', ?, '%') OR 
        l.City LIKE CONCAT('%', ?, '%') OR 
        l.State LIKE CONCAT('%', ?, '%')
    ORDER BY 
        l.Date_Posted DESC
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

<main>
    <h1>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h1>
    <div class="listings-container">
        <?php if (!empty($listings)): ?>
            <?php foreach ($listings as $listing): ?>
                <div class="listing-item">
                    <?php if ($listing['Thumbnail_Image']): ?>
                        <img src="<?php echo htmlspecialchars($listing['Thumbnail_Image']); ?>" alt="<?php echo htmlspecialchars($listing['Title']); ?>">
                    <?php else: ?>
                        <img src="uploads/default-thumbnail.jpg" alt="No Image Available">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($listing['Title']); ?></h3>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($listing['Description']); ?></p>
                    <p><strong>Price:</strong> $<?php echo htmlspecialchars($listing['Price']); ?></p>
                    <p><strong>Posted by:</strong> <?php echo htmlspecialchars($listing['User_Name']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($listing['Category_Name']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($listing['City']); ?>, <?php echo htmlspecialchars($listing['State']); ?></p>
                    <p><strong>Posted On:</strong> <?php echo htmlspecialchars($listing['Formatted_Date']); ?></p>
                    <button class="pill-button" onclick="window.location.href='listing_details.php?listing_id=<?php echo htmlspecialchars($listing['Listing_ID']); ?>'">
                        View Listing
                    </button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No results found for "<?php echo htmlspecialchars($searchQuery); ?>".</p>
        <?php endif; ?>
    </div>
</main>

</body>
<?php include 'footer.php'; ?>
</html>
