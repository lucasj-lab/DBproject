document.addEventListener("DOMContentLoaded", function () {
    const stateDropdown = document.getElementById("state");
    const cityInput = document.getElementById("cityInput");
    const cityDropdown = document.getElementById("cityDropdown");

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

    // Event Listener for State Selection
    stateDropdown.addEventListener("change", function () {
        const selectedState = stateDropdown.value;
        cityInput.value = "";  // Clear previous input
        cityDropdown.innerHTML = "";  // Clear previous dropdown suggestions
        cityDropdown.style.display = "none";

        if (selectedState && statesAndCities[selectedState]) {
            const cities = statesAndCities[selectedState];

            // Filtered city suggestions based on user input
            cityInput.addEventListener("input", function () {
                const searchQuery = cityInput.value.toLowerCase();
                cityDropdown.innerHTML = "";  // Clear previous results

                if (searchQuery.length > 0) {
                    const filteredCities = cities.filter(city =>
                        city.toLowerCase().startsWith(searchQuery)
                    );

                    // Populate dropdown with filtered cities
                    filteredCities.forEach(city => {
                        const option = document.createElement("div");
                        option.textContent = city;
                        option.addEventListener("click", () => {
                            cityInput.value = city;  // Set selected city
                            cityDropdown.innerHTML = "";  // Clear suggestions
                            cityDropdown.style.display = "none";
                        });
                        cityDropdown.appendChild(option);
                    });

                    cityDropdown.style.display = filteredCities.length ? "block" : "none";
                } else {
                    cityDropdown.style.display = "none";
                }
            });
        }
    });

    // Hide dropdown if user clicks outside
    document.addEventListener("click", function (e) {
        if (!cityInput.contains(e.target) && !cityDropdown.contains(e.target)) {
            cityDropdown.style.display = "none";
        }
    });
});