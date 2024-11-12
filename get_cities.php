<?php
if (isset($_GET['state'])) {
    $state = $_GET['state'];
    $apiKey = "RB9qnYzOQyP7TJ5Y9LdfWg==WIxdUUjvSqgzLKpe"; // Replace with your actual API key

    $apiUrl = "https://api.api-ninjas.com/v1/city?state=" . urlencode($state);

    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Api-Key: $apiKey"
    ]);

    // Execute cURL and get the response
    $response = curl_exec($ch);
    curl_close($ch);

    // Check if the API returned a valid response
    if ($response) {
        $cities = json_decode($response, true);
        if (!empty($cities)) {
            foreach ($cities as $city) {
                echo "<option value='" . htmlspecialchars($city['name']) . "'>" . htmlspecialchars($city['name']) . "</option>";
            }
        } else {
            echo "<option value=''>No cities found for this state</option>";
        }
    } else {
        echo "<option value=''>Error retrieving city data</option>";
    }
}

