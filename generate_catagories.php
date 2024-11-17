
<?php

function generateCategoryOptions($conn, $selectedCategory = null) {
    $stmt = $conn->prepare("SELECT Category_ID, Category_Name FROM category");
    $stmt->execute();
    $result = $stmt->get_result();
    $options = "";
    while ($row = $result->fetch_assoc()) {
        $isSelected = ($row['Category_Name'] === $selectedCategory) ? "selected" : "";
        $options .= "<option value=\"{$row['Category_Name']}\" {$isSelected}>{$row['Category_Name']}</option>";
    }
    return $options;
}
?>