<?php
function getAllListings($pdo, $conditions = []) {
    $sql = "
        SELECT 
            Listing_ID, Title, Description, Price, Thumbnail_Image, Date_Posted, 
            State, City, Image_URL
        FROM 
            listings
    ";

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY Date_Posted DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($conditions);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
