<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'database_connection.php';

// Set charset to UTF-8 for proper encoding
$conn->set_charset("utf8");

// Fetch all listings with user, category, and image data
$sql = "
    SELECT 
        listings.Listing_ID, listings.Title, listings.Description, listings.Price, listings.Date_Posted, 
        user.Name AS User_Name, category.Category_Name, listings.State, listings.City, images.Image_URL
    FROM 
        listings
    JOIN 
        user ON listings.User_ID = user.User_ID
    JOIN 
        category ON listings.Category_ID = category.Category_ID
    LEFT JOIN 
        images ON listings.Listing_ID = images.Listing_ID
    ORDER BY 
        listings.Date_Posted DESC
";

$result = $conn->query($sql);

// Prepare listings array
$listings = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $listings[] = $row;
    }
} else {
    // If no listings are found
    $listings = ["message" => "No listings available."];
}

// Output the listings in JSON format
header('Content-Type: application/json');
echo json_encode($listings);

$conn->close();
?>
