<?php
/**
 * Fetch all listings with only the thumbnail image.
 * @param PDO $pdo - The database connection.
 * @param array $conditions - An optional array of conditions for filtering listings.
 * @return array - The fetched listings.
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

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($conditions);

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Debugging: Uncomment this line if you want to log results during development
        // var_dump($result);

        return $result;
    } catch (PDOException $e) {
        error_log("Error fetching all listings: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch all details for a specific listing, including all associated images.
 * @param PDO $pdo - The database connection.
 * @param int $listing_id - The ID of the listing to fetch.
 * @return array - The details of the listing with associated images.
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

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['listing_id' => $listing_id]);

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
                    'Images' => []
                ];
            }
            if (!empty($row['Image_URL'])) {
                $result['Images'][] = $row['Image_URL'];
            }
        }

        return $result;
    } catch (PDOException $e) {
        error_log("Error fetching listing details for ID $listing_id: " . $e->getMessage());
        return [];
    }
}
