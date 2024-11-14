<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Listing</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <h2>Create a New Listing</h2>
    <form id="create-a-listing" class="form-wrapper" action="create_listing.php" method="POST" enctype="multipart/form-data">
        
        <!-- Title Field -->
        <div class="input-container">
            <input type="text" id="title" name="title" placeholder="Title" required>
        </div>

        <!-- Category Dropdown -->
        <div class="input-container">
            <select id="category" name="category" required>
                <option value="">--Select Category--</option>
                <option value="Auto">Auto</option>
                <option value="Electronics">Electronics</option>
                <option value="Furniture">Furniture</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <!-- Description Field -->
        <div class="input-container">
            <textarea id="description" name="description" rows="4" placeholder="Description" required></textarea>
        </div>

        <!-- Price Field -->
        <div class="input-container">
            <input type="number" step="0.01" id="price" name="price" placeholder="Price" required>
        </div>

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
            <input type="text" id="cityInput" placeholder="Type to search for a city" autocomplete="off">
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

        document.getElementById('listing-form').onsubmit = function(event) {
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

</html>