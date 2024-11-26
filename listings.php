<!DOCTYPE html>
<html lang="en">
<?php include 'header.php'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listings</title>
    <style>
        /* Styling for the listing items */
        .listing-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            padding: 20px;
        }

        .listing-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .listing-item img {
            width: 100%;
            max-height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }

        .listing-item h3 {
            font-size: 1.1em;
            margin: 10px 0 5px;
        }

        .listing-item p {
            font-size: 0.9em;
            color: #555;
            margin: 5px 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .listing-item button {
            margin-top: 10px;
            padding: 5px 10px;
            border: none;
            border-radius: 20px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }

        .listing-item button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

    <main class="listings">
        <h1>Listings</h1>
        <div id="listings-container" class="listing-container"></div>

        <script>
            // Function to sanitize dynamic content
            function sanitizeHTML(str) {
                const tempDiv = document.createElement('div');
                tempDiv.textContent = str;
                return tempDiv.innerHTML;
            }

            // Fetch the listings data
            fetch('listings.php?fetchListings=true')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('listings-container');
                    if (data.message) {
                        container.innerHTML = `<p>${sanitizeHTML(data.message)}</p>`;
                        return;
                    }

                    data.forEach(listing => {
                        const listingElement = document.createElement('div');
                        listingElement.className = 'listing-item';

                        const thumbnail = listing.Thumbnail_Image
                            ? `<img src="${sanitizeHTML(listing.Thumbnail_Image)}" alt="${sanitizeHTML(listing.Title)}" class="listing-image">`
                            : `<img src="uploads/default-thumbnail.jpg" alt="No Image Available" class="listing-image">`;

                        // Only show thumbnail, title, and a truncated description
                        listingElement.innerHTML = `
                            ${thumbnail}
                            <h3>${sanitizeHTML(listing.Title)}</h3>
                            <p>${sanitizeHTML(listing.Description).substring(0, 100)}...</p>
                            <button type="button" class="pill-button"
                                onclick="window.location.href='listing_details.php?listing_id=${sanitizeHTML(listing.Listing_ID.toString())}'">
                                View Listing
                            </button>
                        `;

                        container.appendChild(listingElement);
                    });
                })
                .catch(error => {
                    console.error('Error fetching listings:', error);
                    const container = document.getElementById('listings-container');
                    container.innerHTML = `<p>Unable to load listings. Please try again later.</p>`;
                });
        </script>
    </main>

</body>
<?php include 'footer.php'; ?>

</html>
<!DOCTYPE html>
<html lang="en">
<?php include 'header.php'; ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listings</title>
    <style>
        /* Styling for the listing items */
        .listing-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            padding: 20px;
        }

        .listing-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .listing-item img {
            width: 100%;
            height: auto;
            max-height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }

        .listing-item h3 {
            font-size: 1.1em;
            margin: 10px 0 5px;
        }

        .listing-item p {
            font-size: 0.9em;
            color: #555;
            margin: 5px 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .listing-item button {
            margin-top: 10px;
            padding: 5px 10px;
            border: none;
            border-radius: 20px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }

        .listing-item button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

    <main class="listings">
        <h1>Listings</h1>
        <div id="listings-container" class="listing-container"></div>

        <script>
            // Function to sanitize dynamic content
            function sanitizeHTML(str) {
                const tempDiv = document.createElement('div');
                tempDiv.textContent = str;
                return tempDiv.innerHTML;
            }

            // Fetch the listings data
            fetch('listings.php?fetchListings=true')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('listings-container');
                    if (data.message) {
                        container.innerHTML = `<p>${sanitizeHTML(data.message)}</p>`;
                        return;
                    }

                    data.forEach(listing => {
                        const listingElement = document.createElement('div');
                        listingElement.className = 'listing-item';

                        const thumbnail = listing.Thumbnail_Image
                            ? `<img src="${sanitizeHTML(listing.Thumbnail_Image)}" alt="${sanitizeHTML(listing.Title)}" class="listing-image">`
                            : `<img src="uploads/default-thumbnail.jpg" alt="No Image Available" class="listing-image">`;

                        // Only show thumbnail, title, and a truncated description
                        listingElement.innerHTML = `
                            ${thumbnail}
                            <h3>${sanitizeHTML(listing.Title)}</h3>
                            <p>${sanitizeHTML(listing.Description).substring(0, 100)}...</p>
                            <button type="button" class="pill-button"
                                onclick="window.location.href='listing_details.php?listing_id=${sanitizeHTML(listing.Listing_ID.toString())}'">
                                View Listing
                            </button>
                        `;

                        container.appendChild(listingElement);
                    });
                })
                .catch(error => {
                    console.error('Error fetching listings:', error);
                    const container = document.getElementById('listings-container');
                    container.innerHTML = `<p>Unable to load listings. Please try again later.</p>`;
                });
        </script>
    </main>

</body>
<?php include 'footer.php'; ?>

</html>
