
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

        function previewImages() {
            const previewContainer = document.getElementById('preview-container');
            previewContainer.innerHTML = ''; // Clear any previous previews
            const files = document.getElementById('images').files;

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const fileReader = new FileReader();

                fileReader.onload = function (event) {
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
