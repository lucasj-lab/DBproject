<?php
require 'database_connection.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Validate and retrieve the item ID
if (isset($_GET['item']) && is_numeric($_GET['item'])) {
    $item_id = intval($_GET['item']);
} else {
    die("Error: Invalid or missing item ID.");
}

// Fetch the item details
$sql = "
    SELECT 
        l.Title, 
        l.Price, 
        l.Description, 
        u.Name AS Seller_Name
    FROM 
        listings l
    JOIN 
        user u ON l.User_ID = u.User_ID
    WHERE 
        l.Listing_ID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    die("Error: Item not found.");
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Now</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="buy-now-container">
    <h1>Buy Now</h1>
    <div class="buy-now-details">
        <h2><?= htmlspecialchars($item['Title']); ?></h2>
        <p><strong>Price:</strong> $<?= htmlspecialchars(number_format($item['Price'], 2)); ?></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($item['Description']); ?></p>
        <p><strong>Seller:</strong> <?= htmlspecialchars($item['Seller_Name']); ?></p>
    </div>
    <form action="process_purchase.php" method="POST">
        <input type="hidden" name="item_id" value="<?= htmlspecialchars($item_id); ?>">
        <button type="submit" class="btn btn-large">Confirm Purchase</button>
    </form>
</div>

</body>
</html>
