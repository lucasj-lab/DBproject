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

// Initialize query parts
$whereClauses = [];
$params = [];

// Build the WHERE clause based on the search criteria
if (!empty($_GET['title'])) {
    $title = "%" . $conn->real_escape_string($_GET['title']) . "%";
    $whereClauses[] = "listings.Title LIKE ?";
    $params[] = $title;
}

if (!empty($_GET['min_price'])) {
    $min_price = floatval($_GET['min_price']);
    $whereClauses[] = "listings.Price >= ?";
    $params[] = $min_price;
}

if (!empty($_GET['max_price'])) {
    $max_price = floatval($_GET['max_price']);
    $whereClauses[] = "listings.Price <= ?";
    $params[] = $max_price;
}

// Construct the final SQL query
$sql = "SELECT listings.Listing_ID, listings.Title, listings.Description, listings.Price, listings.Date_Posted, 
            user.Name AS User_Name, category.Category_Name, listings.State, listings.City
        FROM listings
        JOIN user ON listings.User_ID = user.User_ID
        JOIN category ON listings.Category_ID = category.Category_ID";

// Append WHERE clauses if there are any
if (count($whereClauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

$sql .= " ORDER BY listings.Date_Posted DESC";

// Prepare and bind parameters
$stmt = $conn->prepare($sql);
if ($stmt) {
    // Dynamically bind parameters
    if (count($params) > 0) {
        $stmt->bind_param(str_repeat("s", count($params)), ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Display the search results
    if ($result->num_rows > 0) {
        echo "<h2>Search Results</h2>";
        while ($listing = $result->fetch_assoc()) {
            echo "<div class='listing'>";
            echo "<h3>" . htmlspecialchars($listing['Title']) . "</h3>";
            echo "<p><strong>Price:</strong> $" . htmlspecialchars($listing['Price']) . "</p>";
            echo "<p><strong>Description:</strong> " . htmlspecialchars($listing['Description']) . "</p>";
            echo "<p><strong>Posted by:</strong> " . htmlspecialchars($listing['User_Name']) . "</p>";
            echo "<p><strong>Category:</strong> " . htmlspecialchars($listing['Category_Name']) . "</p>";
            echo "<p><strong>Location:</strong> " . htmlspecialchars($listing['City'] . ', ' . $listing['State']) . "</p>";
            echo "<p><strong>Date Posted:</strong> " . htmlspecialchars($listing['Date_Posted']) . "</p>";
            echo "<a href='listing_details.php?id=" . $listing['Listing_ID'] . "'>View Details</a>";
            echo "</div>";
        }
    } else {
        echo "<p>No listings found matching your criteria.</p>";
    }

    $stmt->close();
} else {
    echo "Query error: " . $conn->error;
}

$conn->close();
?>
