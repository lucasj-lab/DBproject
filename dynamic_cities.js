// dynamic_cities.js

document.addEventListener("DOMContentLoaded", function () {
    const stateDropdown = document.getElementById("state");
    const cityDropdown = document.getElementById("city");

    stateDropdown.addEventListener("change", function () {
        const selectedState = stateDropdown.value;
        
        if (selectedState) {
            fetchCities(selectedState);
        } else {
            cityDropdown.innerHTML = '<option value="">--Select City--</option>';
        }
    });

    function fetchCities(stateCode) {
        // Clear the city dropdown
        cityDropdown.innerHTML = '<option value="">Loading cities...</option>';

        // Fetch cities from get_cities.php
        fetch(`get_cities.php?state=${encodeURIComponent(stateCode)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            cityDropdown.innerHTML = data;
        })
        .catch(error => {
            console.error('Error fetching cities:', error);
            cityDropdown.innerHTML = '<option value="">Error loading cities</option>';
        });
    }
});
