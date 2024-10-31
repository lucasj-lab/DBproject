<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1); 

// Database connection parameters
$servername = "database-1-instance-1.cpgoq8m2kfkd.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "Bagflea3!";
$dbname = "CraigslistDB";

// Create a new connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    // Send JSON response if the connection fails
    header('Content-Type: application/json');
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

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
    // Send a message if no listings found
    $listings = ["message" => "No listings available."];
}

// Output the listings in JSON format
header('Content-Type: application/json');
echo json_encode($listings);

$conn->close();
?>
