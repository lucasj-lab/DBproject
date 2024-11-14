<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'database_connection.php';

// Function to get Category_ID from Category table
function getCategoryID($conn, $categoryName)
{
    $stmt = $conn->prepare("SELECT Category_ID FROM category WHERE Category_Name = ?");
    $stmt->bind_param("s", $categoryName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['Category_ID'];
    } else {
        return false; // Category not found
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_email = $_POST['user_email'];
    $category = $_POST['category'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $state = $_POST['state'];
    $city = $_POST['city'] ?? $_POST['city-input'];


    // Get the User_ID using the email
    $stmt = $conn->prepare("SELECT User_ID FROM user WHERE Email = ?");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id);
        $stmt->fetch();

        // Get the Category_ID using the function
        $category_id = getCategoryID($conn, $category);

        if ($category_id === false) {
            echo json_encode(['success' => false, 'message' => 'Invalid category selected.']);
        } else {
            // Insert new listing
            $stmt = $conn->prepare("INSERT INTO listings (Title, Description, Price, Date_Posted, User_ID, Category_ID, State, City) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)");
            $stmt->bind_param("ssissss", $title, $description, $price, $user_id, $category_id, $state, $city);

            if ($stmt->execute()) {
                $listing_id = $stmt->insert_id; // Get the ID of the new listing

                // Check if images were uploaded
                if (!empty($_FILES['images']['name'][0])) {
                    $uploadDirectory = 'uploads/';
                    if (!is_dir($uploadDirectory)) {
                        mkdir($uploadDirectory, 0777, true);
                    }

                    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                        $imageName = basename($_FILES['images']['name'][$key]);
                        $targetFilePath = $uploadDirectory . $imageName;

                        if (move_uploaded_file($tmpName, $targetFilePath)) {
                            $imageUrl = $targetFilePath;

                            // Insert image data into images table
                            $imageSql = "INSERT INTO images (image_url, listing_id) VALUES (?, ?)";
                            $imgStmt = $conn->prepare($imageSql);
                            $imgStmt->bind_param("si", $imageUrl, $listing_id);
                            $imgStmt->execute();
                        }
                    }
                }

                // Return success message
                echo json_encode(['success' => true, 'message' => 'Listing created successfully! <a href=\'account.php\'> Click here to view your listings.</a>']);

            } else {
                echo json_encode(['success' => false, 'message' => 'Database error: Unable to create listing.']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User email not found.']);
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Listing</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <div class="logo">
            <h1>Create New Listing</h1>
        </div>

        <!-- Desktop Navigation Menu -->
        <nav>
            <ul class="desktop-menu">
                <li><a href="index.html">Home</a></li>
                <li><a href="create_listing.html">New Listing</a></li>
                <li><a href="listings.html">View All Listings</a></li>
                <li><a href="login.html">Login</a></li>
                <li><a href="register.html">Register</a></li>
                <li><a href="about.html">About</a></li>
            </ul>
        </nav>

        <!-- User Icon for User Dashboard -->
        <div class="user-icon">
            <a href="user_dashboard.php">U</a> <!-- "U" as a placeholder for the user icon -->
        </div>

        <!-- Hamburger Menu Icon for Mobile View -->
        <div class="hamburger" onclick="toggleMobileMenu()">☰</div>

        <!-- Mobile Dropdown Menu for Smaller Screens -->
        <div class="mobile-menu" id="mobileMenu">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="create_listing.html">New Listing</a></li>
                <li><a href="listings.html">View All Listings</a></li>
                <li><a href="login.html">Login</a></li>
                <li><a href="register.html">Register</a></li>
                <li><a href="about.html">About</a></li>
            </ul>
        </div>
    </header>


    <div class="post-ad">
        <h2>Post Your Ad</h2>
        <p>Please <a href="register.html">register</a> to create a new listing.</p>
        <form id="listing-form" action="create_listing.php" method="POST" enctype="multipart/form-data">

            <div class="listing-form-group">
                <input type="text" id="user_email" name="user_email" placeholder="Email" required>

                <select id="category" name="category" required>
                    <option value="">--Select Category--</option>
                    <option value="Auto">Auto</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Furniture">Furniture</option>
                    <option value="Other">Other</option>
                </select>

                <input type="text" id="title" name="title" placeholder="Title" required>

                <textarea id="description" name="description" rows="4" placeholder="Description" required></textarea>

                <input type="number" step="0.01" id="price" name="price" placeholder="Price" required>

                <select id="state" name="state" onchange="updateCities()" required>
                    <!-- State Dropdown -->
                    <div class="input-container">
                        <select id="state" name="state" required>
                            <option value="">--Select State--</option>
                            <option value="AL">Alabama</option>
                            <option value="AK">Alaska</option>
                            <option value="AZ">Arizona</option>
                            <option value="AR">Arkansas</option>
                            <option value="CA">California</option>
                            <option value="CO">Colorado</option>
                            <option value="CT">Connecticut</option>
                            <option value="DE">Delaware</option>
                            <option value="FL">Florida</option>
                            <option value="GA">Georgia</option>
                            <option value="HI">Hawaii</option>
                            <option value="ID">Idaho</option>
                            <option value="IL">Illinois</option>
                            <option value="IN">Indiana</option>
                            <option value="IA">Iowa</option>
                            <option value="KS">Kansas</option>
                            <option value="KY">Kentucky</option>
                            <option value="LA">Louisiana</option>
                            <option value="ME">Maine</option>
                            <option value="MD">Maryland</option>
                            <option value="MA">Massachusetts</option>
                            <option value="MI">Michigan</option>
                            <option value="MN">Minnesota</option>
                            <option value="MS">Mississippi</option>
                            <option value="MO">Missouri</option>
                            <option value="MT">Montana</option>
                            <option value="NE">Nebraska</option>
                            <option value="NV">Nevada</option>
                            <option value="NH">New Hampshire</option>
                            <option value="NJ">New Jersey</option>
                            <option value="NM">New Mexico</option>
                            <option value="NY">New York</option>
                            <option value="NC">North Carolina</option>
                            <option value="ND">North Dakota</option>
                            <option value="OH">Ohio</option>
                            <option value="OK">Oklahoma</option>
                            <option value="OR">Oregon</option>
                            <option value="PA">Pennsylvania</option>
                            <option value="RI">Rhode Island</option>
                            <option value="SC">South Carolina</option>
                            <option value="SD">South Dakota</option>
                            <option value="TN">Tennessee</option>
                            <option value="TX">Texas</option>
                            <option value="UT">Utah</option>
                            <option value="VT">Vermont</option>
                            <option value="VA">Virginia</option>
                            <option value="WA">Washington</option>
                            <option value="WV">West Virginia</option>
                            <option value="WI">Wisconsin</option>
                            <option value="WY">Wyoming</option>
                        </select>
                </select>

                <div class="listing-city-group">
                    <select id="city-dropdown" name="city" onchange="toggleInput()" required>
                        <option value="">--Select City--</option>
                    </select>
                    <input type="text" id="city-input" name="city-input" placeholder="Type your city here if not listed"
                        oninput="clearDropdown()" />
                </div>

                <label for="images">Upload Images:</label>
                <input type="file" id="images" name="images[]" multiple>

                <button type="submit">Submit</button>
            </div>
        </form>
    </div>


    <script>
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById("mobileMenu");
            mobileMenu.classList.toggle("active");
        }

        const statesAndCities = {
        "Alabama": ["Birmingham", "Montgomery", "Mobile", "Huntsville", "Tuscaloosa"],
        "Alaska": ["Anchorage", "Fairbanks", "Juneau", "Sitka", "Ketchikan"],
        "Arizona": ["Phoenix", "Tucson", "Mesa", "Chandler", "Glendale"],
        "Arkansas": ["Little Rock", "Fort Smith", "Fayetteville", "Springdale", "Jonesboro"],
        "California": ["Los Angeles", "San Diego", "San Jose", "San Francisco", "Fresno"],
        "Colorado": ["Denver", "Colorado Springs", "Aurora", "Fort Collins", "Lakewood"],
        "Connecticut": ["Bridgeport", "New Haven", "Stamford", "Hartford", "Waterbury"],
        "Delaware": ["Wilmington", "Dover", "Newark", "Middletown", "Smyrna"],
        "Florida": ["Jacksonville", "Miami", "Tampa", "Orlando", "St. Petersburg"],
        "Georgia": ["Atlanta", "Augusta", "Columbus", "Macon", "Savannah"],
        "Hawaii": ["Honolulu", "Hilo", "Kailua", "Kapolei", "Kaneohe"],
        "Idaho": ["Boise", "Meridian", "Nampa", "Idaho Falls", "Pocatello"],
        "Illinois": ["Chicago", "Aurora", "Naperville", "Joliet", "Rockford"],
        "Indiana": ["Indianapolis", "Fort Wayne", "Evansville", "South Bend", "Carmel"],
        "Iowa": ["Des Moines", "Cedar Rapids", "Davenport", "Sioux City", "Iowa City"],
        "Kansas": ["Wichita", "Overland Park", "Kansas City", "Olathe", "Topeka"],
        "Kentucky": ["Louisville", "Lexington", "Bowling Green", "Owensboro", "Covington"],
        "Louisiana": ["New Orleans", "Baton Rouge", "Shreveport", "Lafayette", "Lake Charles"],
        "Maine": ["Portland", "Lewiston", "Bangor", "South Portland", "Auburn"],
        "Maryland": ["Baltimore", "Frederick", "Rockville", "Gaithersburg", "Bowie"],
        "Massachusetts": ["Boston", "Worcester", "Springfield", "Lowell", "Cambridge"],
        "Michigan": ["Detroit", "Grand Rapids", "Warren", "Sterling Heights", "Ann Arbor"],
        "Minnesota": ["Minneapolis", "Saint Paul", "Rochester", "Duluth", "Bloomington"],
        "Mississippi": ["Jackson", "Gulfport", "Southaven", "Hattiesburg", "Biloxi"],
        "Missouri": ["Kansas City", "St. Louis", "Springfield", "Columbia", "Independence"],
        "Montana": ["Billings", "Missoula", "Great Falls", "Bozeman", "Butte"],
        "Nebraska": ["Omaha", "Lincoln", "Bellevue", "Grand Island", "Kearney"],
        "Nevada": ["Las Vegas", "Henderson", "Reno", "North Las Vegas", "Sparks"],
        "New Hampshire": ["Manchester", "Nashua", "Concord", "Derry", "Dover"],
        "New Jersey": ["Newark", "Jersey City", "Paterson", "Elizabeth", "Edison"],
        "New Mexico": ["Albuquerque", "Las Cruces", "Rio Rancho", "Santa Fe", "Roswell"],
        "New York": ["New York City", "Buffalo", "Rochester", "Yonkers", "Syracuse"],
        "North Carolina": ["Charlotte", "Raleigh", "Greensboro", "Durham", "Winston-Salem"],
        "North Dakota": ["Fargo", "Bismarck", "Grand Forks", "Minot", "West Fargo"],
        "Ohio": ["Columbus", "Cleveland", "Cincinnati", "Toledo", "Akron"],
        "Oklahoma": ["Oklahoma City", "Tulsa", "Norman", "Broken Arrow", "Lawton"],
        "Oregon": ["Portland", "Salem", "Eugene", "Gresham", "Hillsboro"],
        "Pennsylvania": ["Philadelphia", "Pittsburgh", "Allentown", "Erie", "Reading"],
        "Rhode Island": ["Providence", "Warwick", "Cranston", "Pawtucket", "East Providence"],
        "South Carolina": ["Charleston", "Columbia", "North Charleston", "Mount Pleasant", "Rock Hill"],
        "South Dakota": ["Sioux Falls", "Rapid City", "Aberdeen", "Brookings", "Watertown"],
        "Tennessee": ["Memphis", "Nashville", "Knoxville", "Chattanooga", "Clarksville"],
        "Texas": ["Houston", "San Antonio", "Dallas", "Austin", "Fort Worth"],
        "Utah": ["Salt Lake City", "West Valley City", "Provo", "West Jordan", "Orem"],
        "Vermont": ["Burlington", "South Burlington", "Rutland", "Barre", "Montpelier"],
        "Virginia": ["Virginia Beach", "Norfolk", "Chesapeake", "Richmond", "Newport News"],
        "Washington": ["Seattle", "Spokane", "Tacoma", "Vancouver", "Bellevue"],
        "West Virginia": ["Charleston", "Huntington", "Morgantown", "Parkersburg", "Wheeling"],
        "Wisconsin": ["Milwaukee", "Madison", "Green Bay", "Kenosha", "Racine"],
        "Wyoming": ["Cheyenne", "Casper", "Laramie", "Gillette", "Rock Springs"]
      };

        function updateCities() {
            const stateSelect = document.getElementById('state');
            const cityDropdown = document.getElementById('city-dropdown');
            const cityInput = document.getElementById('city-input');
            const selectedState = stateSelect.value;

            cityDropdown.innerHTML = '<option value="">--Select City--</option>';
            cityInput.value = "";

            if (selectedState) {
                const cities = citiesByState[selectedState] || [];
                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    cityDropdown.appendChild(option);
                });
            }
        }

        function toggleInput() {
            const cityDropdown = document.getElementById('city-dropdown');
            const cityInput = document.getElementById('city-input');

            if (cityDropdown.value === "") {
                cityInput.style.display = "block";
            } else {
                cityInput.style.display = "none";
            }
        }

        function clearDropdown() {
            const cityDropdown = document.getElementById('city-dropdown');
            cityDropdown.value = "";
            toggleInput();
        }

        document.getElementById('listing-form').onsubmit = function (event) {
            event.preventDefault();

            const formData = new FormData(this);
            fetch('create_listing.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    const messageDiv = document.createElement('div');
                    messageDiv.id = 'message';
                    messageDiv.style.display = 'block';

                    if (data.success) {
                        messageDiv.style.color = 'green';
                        messageDiv.textContent = 'Listing created successfully!';
                        this.reset();
                    } else {
                        messageDiv.style.color = 'red';
                        messageDiv.textContent = data.message || 'Failed to create listing.';
                    }
                    document.body.prepend(messageDiv);
                })
                .catch(error => {
                    console.error('Error:', error);
                    const messageDiv = document.createElement('div');
                    messageDiv.id = 'message';
                    messageDiv.style.display = 'block';
                    messageDiv.style.color = 'red';
                    messageDiv.textContent = 'An error occurred while creating the listing.';
                    document.body.prepend(messageDiv);
                });
        };
    </script>

    <style>
        /* General Reset and Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f5f5f5;
            color: #333;
        }

        header {
            background-color: #1a73e8;
            color: #fff;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .logo h1 {
            margin-left: 3em;
            font-size: 1.8rem;

        }

        .desktop-menu {
            display: flex;
            list-style: none;
        }

        .desktop-menu li {
            margin-left: 1rem;
        }

        .desktop-menu li a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }

        /* Hamburger Menu for Mobile */
        .hamburger {
            display: none;
            font-size: 1.8rem;
            color: #fff;
            cursor: pointer;
        }

        .mobile-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: #1a73e8;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0.2rem 0.2rem 0.5rem rgba(0, 0, 0, 0.1);
        }

        .mobile-menu.active {
            display: block;
        }

        .mobile-menu ul {
            display: flex;
            flex-direction: column;
            list-style: none;
        }

        .mobile-menu ul li {
            margin-bottom: 1rem;
        }

        .mobile-menu ul li a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .desktop-menu {
                display: none;
            }

            .hamburger {
                display: block;
            }
        }

        /* Listings Section */
        .listing-item {
            background-color: #fff;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .listing-item h3 {
            margin: 0.5rem 0;
        }
    </style>

</body>

</html>
<!-- State Dropdown -->
<div class="input-container">
    <select id="state" name="state" required>
        <option value="">--Select State--</option>
        <option value="AL">Alabama</option>
        <option value="AK">Alaska</option>
        <option value="AZ">Arizona</option>
        <option value="AR">Arkansas</option>
        <option value="CA">California</option>
        <option value="CO">Colorado</option>
        <option value="CT">Connecticut</option>
        <option value="DE">Delaware</option>
        <option value="FL">Florida</option>
        <option value="GA">Georgia</option>
        <option value="HI">Hawaii</option>
        <option value="ID">Idaho</option>
        <option value="IL">Illinois</option>
        <option value="IN">Indiana</option>
        <option value="IA">Iowa</option>
        <option value="KS">Kansas</option>
        <option value="KY">Kentucky</option>
        <option value="LA">Louisiana</option>
        <option value="ME">Maine</option>
        <option value="MD">Maryland</option>
        <option value="MA">Massachusetts</option>
        <option value="MI">Michigan</option>
        <option value="MN">Minnesota</option>
        <option value="MS">Mississippi</option>
        <option value="MO">Missouri</option>
        <option value="MT">Montana</option>
        <option value="NE">Nebraska</option>
        <option value="NV">Nevada</option>
        <option value="NH">New Hampshire</option>
        <option value="NJ">New Jersey</option>
        <option value="NM">New Mexico</option>
        <option value="NY">New York</option>
        <option value="NC">North Carolina</option>
        <option value="ND">North Dakota</option>
        <option value="OH">Ohio</option>
        <option value="OK">Oklahoma</option>
        <option value="OR">Oregon</option>
        <option value="PA">Pennsylvania</option>
        <option value="RI">Rhode Island</option>
        <option value="SC">South Carolina</option>
        <option value="SD">South Dakota</option>
        <option value="TN">Tennessee</option>
        <option value="TX">Texas</option>
        <option value="UT">Utah</option>
        <option value="VT">Vermont</option>
        <option value="VA">Virginia</option>
        <option value="WA">Washington</option>
        <option value="WV">West Virginia</option>
        <option value="WI">Wisconsin</option>
        <option value="WY">Wyoming</option>
    </select>
</div>

<!-- City Autocomplete Input and Dropdown -->
<div class="input-container">
    <div id="cityDropdown" class="dropdown-content"></div>
</div>

<!-- File Upload Section -->
<div class="input-container file-upload-container">
    <input type="file" id="fileInput" name="files[]" class="file-input" multiple>
    <label for="fileInput" class="file-upload-button">Choose Files</label>
</div>

<!-- Submit Button -->
<div class="input-container">
    <button type="submit" class="submit-button">Create Listing</button>
</div>
</form>

<?php include 'footer.php'; ?>
<script>
    function toggleMobileMenu() {
        const mobileMenu = document.getElementById("mobileMenu");
        mobileMenu.classList.toggle("active");
    }

    const statesAndCities = {
        "Alabama": ["Birmingham", "Montgomery", "Mobile", "Huntsville", "Tuscaloosa"],
        "Alaska": ["Anchorage", "Fairbanks", "Juneau", "Sitka", "Ketchikan"],
        "Arizona": ["Phoenix", "Tucson", "Mesa", "Chandler", "Glendale"],
        "Arkansas": ["Little Rock", "Fort Smith", "Fayetteville", "Springdale", "Jonesboro"],
        "California": ["Los Angeles", "San Diego", "San Jose", "San Francisco", "Fresno"],
        "Colorado": ["Denver", "Colorado Springs", "Aurora", "Fort Collins", "Lakewood"],
        "Connecticut": ["Bridgeport", "New Haven", "Stamford", "Hartford", "Waterbury"],
        "Delaware": ["Wilmington", "Dover", "Newark", "Middletown", "Smyrna"],
        "Florida": ["Jacksonville", "Miami", "Tampa", "Orlando", "St. Petersburg"],
        "Georgia": ["Atlanta", "Augusta", "Columbus", "Macon", "Savannah"],
        "Hawaii": ["Honolulu", "Hilo", "Kailua", "Kapolei", "Kaneohe"],
        "Idaho": ["Boise", "Meridian", "Nampa", "Idaho Falls", "Pocatello"],
        "Illinois": ["Chicago", "Aurora", "Naperville", "Joliet", "Rockford"],
        "Indiana": ["Indianapolis", "Fort Wayne", "Evansville", "South Bend", "Carmel"],
        "Iowa": ["Des Moines", "Cedar Rapids", "Davenport", "Sioux City", "Iowa City"],
        "Kansas": ["Wichita", "Overland Park", "Kansas City", "Olathe", "Topeka"],
        "Kentucky": ["Louisville", "Lexington", "Bowling Green", "Owensboro", "Covington"],
        "Louisiana": ["New Orleans", "Baton Rouge", "Shreveport", "Lafayette", "Lake Charles"],
        "Maine": ["Portland", "Lewiston", "Bangor", "South Portland", "Auburn"],
        "Maryland": ["Baltimore", "Frederick", "Rockville", "Gaithersburg", "Bowie"],
        "Massachusetts": ["Boston", "Worcester", "Springfield", "Lowell", "Cambridge"],
        "Michigan": ["Detroit", "Grand Rapids", "Warren", "Sterling Heights", "Ann Arbor"],
        "Minnesota": ["Minneapolis", "Saint Paul", "Rochester", "Duluth", "Bloomington"],
        "Mississippi": ["Jackson", "Gulfport", "Southaven", "Hattiesburg", "Biloxi"],
        "Missouri": ["Kansas City", "St. Louis", "Springfield", "Columbia", "Independence"],
        "Montana": ["Billings", "Missoula", "Great Falls", "Bozeman", "Butte"],
        "Nebraska": ["Omaha", "Lincoln", "Bellevue", "Grand Island", "Kearney"],
        "Nevada": ["Las Vegas", "Henderson", "Reno", "North Las Vegas", "Sparks"],
        "New Hampshire": ["Manchester", "Nashua", "Concord", "Derry", "Dover"],
        "New Jersey": ["Newark", "Jersey City", "Paterson", "Elizabeth", "Edison"],
        "New Mexico": ["Albuquerque", "Las Cruces", "Rio Rancho", "Santa Fe", "Roswell"],
        "New York": ["New York City", "Buffalo", "Rochester", "Yonkers", "Syracuse"],
        "North Carolina": ["Charlotte", "Raleigh", "Greensboro", "Durham", "Winston-Salem"],
        "North Dakota": ["Fargo", "Bismarck", "Grand Forks", "Minot", "West Fargo"],
        "Ohio": ["Columbus", "Cleveland", "Cincinnati", "Toledo", "Akron"],
        "Oklahoma": ["Oklahoma City", "Tulsa", "Norman", "Broken Arrow", "Lawton"],
        "Oregon": ["Portland", "Salem", "Eugene", "Gresham", "Hillsboro"],
        "Pennsylvania": ["Philadelphia", "Pittsburgh", "Allentown", "Erie", "Reading"],
        "Rhode Island": ["Providence", "Warwick", "Cranston", "Pawtucket", "East Providence"],
        "South Carolina": ["Charleston", "Columbia", "North Charleston", "Mount Pleasant", "Rock Hill"],
        "South Dakota": ["Sioux Falls", "Rapid City", "Aberdeen", "Brookings", "Watertown"],
        "Tennessee": ["Memphis", "Nashville", "Knoxville", "Chattanooga", "Clarksville"],
        "Texas": ["Houston", "San Antonio", "Dallas", "Austin", "Fort Worth"],
        "Utah": ["Salt Lake City", "West Valley City", "Provo", "West Jordan", "Orem"],
        "Vermont": ["Burlington", "South Burlington", "Rutland", "Barre", "Montpelier"],
        "Virginia": ["Virginia Beach", "Norfolk", "Chesapeake", "Richmond", "Newport News"],
        "Washington": ["Seattle", "Spokane", "Tacoma", "Vancouver", "Bellevue"],
        "West Virginia": ["Charleston", "Huntington", "Morgantown", "Parkersburg", "Wheeling"],
        "Wisconsin": ["Milwaukee", "Madison", "Green Bay", "Kenosha", "Racine"],
        "Wyoming": ["Cheyenne", "Casper", "Laramie", "Gillette", "Rock Springs"]
    };


    function updateCities() {
        const stateSelect = document.getElementById('state');
        const cityDropdown = document.getElementById('city-dropdown');
        const cityInput = document.getElementById('city-input');
        const selectedState = stateSelect.value;

        cityDropdown.innerHTML = '<option value="">--Select City--</option>';
        cityInput.value = "";

        if (selectedState) {
            const cities = citiesByState[selectedState] || [];
            cities.forEach(city => {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                cityDropdown.appendChild(option);
            });
        }
    }

    function toggleInput() {
        const cityDropdown = document.getElementById('city-dropdown');
        const cityInput = document.getElementById('city-input');

        if (cityDropdown.value === "") {
            cityInput.style.display = "block";
        } else {
            cityInput.style.display = "none";
        }
    }

    function clearDropdown() {
        const cityDropdown = document.getElementById('city-dropdown');
        cityDropdown.value = "";
        toggleInput();
    }

    document.getElementById('listing-form').onsubmit = function (event) {
        event.preventDefault();

        const formData = new FormData(this);
        fetch('create_listing.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.createElement('div');
                messageDiv.id = 'message';
                messageDiv.style.display = 'block';

                if (data.success) {
                    messageDiv.style.color = 'green';
                    messageDiv.textContent = 'Listing created successfully!';
                    this.reset();
                } else {
                    messageDiv.style.color = 'red';
                    messageDiv.textContent = data.message || 'Failed to create listing.';
                }
                document.body.prepend(messageDiv);
            })
            .catch(error => {
                console.error('Error:', error);
                const messageDiv = document.createElement('div');
                messageDiv.id = 'message';
                messageDiv.style.display = 'block';
                messageDiv.style.color = 'red';
                messageDiv.textContent = 'An error occurred while creating the listing.';
                document.body.prepend(messageDiv);
            });
    };
</script>

</body>

<?php include 'footer.php'; ?>

</html>