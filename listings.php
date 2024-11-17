<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'database_connection.php';

function getAllListings($conn) {
    $sql = "
        SELECT 
            l.Listing_ID, l.Title, l.Description, l.Price, l.Date_Posted, 
            l.State, l.City, c.Category_Name, u.Name AS User_Name,
            GROUP_CONCAT(i.Image_URL) AS Images
        FROM 
            listings l
        LEFT JOIN 
            category c ON l.Category_ID = c.Category_ID
        LEFT JOIN 
            user u ON l.User_ID = u.User_ID
        LEFT JOIN 
            images i ON l.Listing_ID = i.Listing_ID
        GROUP BY 
            l.Listing_ID
        ORDER BY 
            l.Date_Posted DESC
    ";

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
