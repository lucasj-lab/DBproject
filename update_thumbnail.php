if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newThumbnail = $_POST['thumbnail'];
    $listingId = $_POST['listing_id'];

    $sql = "UPDATE listings SET Thumbnail_Image = :thumbnail WHERE Listing_ID = :listing_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['thumbnail' => $newThumbnail, 'listing_id' => $listingId]);

    header("Location: user_dashboard.php?msg=Thumbnail updated successfully");
    exit();
}
