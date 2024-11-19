<?php

require 'database_connection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!$stmt) {
    error_log("SQL preparation failed: " . $conn->error);
    die("SQL Error: " . $conn->error);
}


// Function to fetch all listings
function getAllListings($conn) {
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


    // Debugging log for the query
    error_log("Executing SQL Query: $sql");

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("SQL preparation failed: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $listings = [];
    while ($row = $result->fetch_assoc()) {
        $listings[] = $row;
    }

    return $listings;
}

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
        echo json_encode(["error" => "Error fetching listings."]);
    }
    exit();
}
?>
