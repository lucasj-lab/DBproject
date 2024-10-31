<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "database-1-instance-1.cpgoq8m2kfkd.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "Bagflea3!";
$dbname = "CraigslistDB";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the Listing_ID is set in the URL
if (isset($_GET['id'])) {
    $listing_id = intval($_GET['id']);

    // Query to fetch listing details
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
        WHERE 
            listings.Listing_ID = $listing_id
    ";

    $result = $conn->query($sql);
    if ($result === false) {
        echo "Query error: " . $conn->error;
        exit;
    } elseif ($result->num_rows > 0) {
        $listing = $result->fetch_assoc();
    } else {
        // If no listing is found, redirect to a not-found page or show an error
        header("Location: not_found.html");
        exit;
    }
} else {
    // If no ID is passed, redirect to a generic error page or show a message
    header("Location: error.html");
    exit;
}

// Close the database connection
$conn->close();
?>

