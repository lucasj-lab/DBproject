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
                    <option value="">--Select State--</option>
                    <option value="AL">Alabama</option>
                    <option value="AK">Alaska</option>
                    <option value="AZ">Arizona</option>
                    <option value="AR">Arkansas</option>
                    <option value="CA">California</option>
                    <!-- Add other states as needed -->
                </select>
    
                <div class="listing-city-group">
                    <select id="city-dropdown" name="city" onchange="toggleInput()" required>
                        <option value="">--Select City--</option>
                    </select>
                    <input type="text" id="city-input" name="city-input" placeholder="Type your city here if not listed" oninput="clearDropdown()" />
                </div>
    
                <label for="images">Upload Images:</label>
                <input type="file" id="images" name="images[]" multiple accept=".jpg, .jpeg, .png, .gif, .heic, .heif">
                
    
                <button type="submit">Submit</button>
            </div>
        </form>
    </div>
    

    <footer>
        <p>&copy; 2024 Rookies 2.0 | All rights reserved.</p>
        <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById("mobileMenu");
            mobileMenu.classList.toggle("active");
        }

        const citiesByState = {
            'AL': ['Birmingham', 'Montgomery', 'Huntsville', 'Mobile', 'Tuscaloosa'],
            'AK': ['Anchorage', 'Fairbanks', 'Juneau', 'Sitka', 'Ketchikan'],
            'AZ': ['Phoenix', 'Tucson', 'Mesa', 'Chandler', 'Scottsdale'],
            'AR': ['Little Rock', 'Fort Smith', 'Fayetteville', 'Springdale', 'Jonesboro'],
            'CA': ['Los Angeles', 'San Francisco', 'San Diego', 'San Jose', 'Sacramento'],
            'CO': ['Denver', 'Colorado Springs', 'Aurora', 'Fort Collins', 'Lakewood'],
            'CT': ['Hartford', 'New Haven', 'Stamford', 'Bridgeport', 'Waterbury'],
            'DE': ['Wilmington', 'Dover', 'Newark', 'Middletown', 'Smyrna'],
            'FL': ['Jacksonville', 'Miami', 'Tampa', 'Orlando', 'St. Petersburg'],
            'GA': ['Atlanta', 'Augusta', 'Columbus', 'Savannah', 'Athens']
            // Add other states and cities as needed
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

        function previewImages() {
    const previewContainer = document.getElementById('preview-container');
    previewContainer.innerHTML = ''; // Clear any previous previews
    const files = document.getElementById('images').files;

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const fileReader = new FileReader();

        fileReader.onload = function(event) {
            const img = document.createElement('img');
            img.src = event.target.result;
            img.style.width = '100px';
            img.style.margin = '5px';
            previewContainer.appendChild(img);
        };

        fileReader.readAsDataURL(file);
    }
}

document.getElementById('images').addEventListener('change', previewImages);
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