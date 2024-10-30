<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection parameters
$servername = "database-1.c5qwuo6qo0y3.us-east-2.rds.amazonaws.com";
$username = "admin";
$password = "Imtheman198627*";
$dbname = "new_craigslist_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

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

$listings = [];
if ($result && $result->num_rows > 0) {
    // Store all listings in an array
    while ($row = $result->fetch_assoc()) {
        $listings[] = $row;
    }
}

// Output listings in JSON format
header('Content-Type: application/json');
echo json_encode($listings);

$conn->close();
?>



