<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'database_connection.php';
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
            categories.Category_Name,
            users.Name AS User_Name,
            images.Image_URL AS Thumbnail_Image
        FROM listings
        LEFT JOIN categories ON listings.Category_ID = categories.Category_ID
        LEFT JOIN users ON listings.User_ID = users.User_ID
        LEFT JOIN images ON listings.Listing_ID = images.Listing_ID AND images.Is_Thumbnail = 1
        ORDER BY listings.Date_Posted DESC
    ";

    error_log("Executing query: $sql");

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    error_log("Query Results: " . json_encode($results));

    return $results;
}

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $listings = [];
    while ($row = $result->fetch_assoc()) {
        $row['Images'] = $row['Images'] ? explode(',', $row['Images']) : [];
        $listings[] = $row;
    }

    $stmt->close();
    return $listings;
}

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
        echo json_encode(["error" => "Database error. Please try again later."]);
    }
    exit();
}
?>
