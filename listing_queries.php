<?php
/**
 * Fetch all listings with only the thumbnail image.
 */
function getAllListings($pdo, $conditions = []) {
    $sql = "
        SELECT 
            Listing_ID, Title, Description, Price, Thumbnail_Image, Date_Posted, 
            State, City
        FROM 
            listings
    ";

    // Add conditions if provided
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY Date_Posted DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($conditions);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch all details for a specific listing, including all associated images.
 */
function getListingDetails($pdo, $listing_id) {
    $sql = "
        SELECT 
            listings.Listing_ID, Title, Description, Price, Thumbnail_Image, Date_Posted, 
            State, City, images.Image_URL
        FROM 
            listings
        LEFT JOIN 
            images ON listings.Listing_ID = images.Listing_ID
        WHERE 
            listings.Listing_ID = :listing_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['listing_id' => $listing_id]);

    // Group data for listing details
    $result = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (empty($result)) {
            $result = [
                'Listing_ID' => $row['Listing_ID'],
                'Title' => $row['Title'],
                'Description' => $row['Description'],
                'Price' => $row['Price'],
                'Thumbnail_Image' => $row['Thumbnail_Image'],
                'Date_Posted' => $row['Date_Posted'],
                'State' => $row['State'],
                'City' => $row['City'],
                'Images' => [] // Initialize empty array for additional images
            ];
        }

        // Add images to the Images array
        if (!empty($row['Image_URL'])) {
            $result['Images'][] = $row['Image_URL'];
        }
    }

    return $result;
}
?>
