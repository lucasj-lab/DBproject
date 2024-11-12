<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Listing</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include 'header.php'; ?>


    <form id="create-a-listing" class="form-wrapper" action="create_listing.php" method="POST" enctype="multipart/form-data">
    <h2>Create a New Listing</h2>
        <div class="input-container">
            <input type="text" id="title" name="title" placeholder="Title" required>
        </div>

        <div class="input-container">
            <select id="category" name="category" required>
                <option value="">--Select Category--</option>
                <option value="Auto">Auto</option>
                <option value="Electronics">Electronics</option>
                <option value="Furniture">Furniture</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="input-container">
            <textarea id="description" name="description" rows="4" placeholder="Description" required></textarea>
        </div>

        <div class="input-container">
            <input type="number" step="0.01" id="price" name="price" placeholder="Price" required>
        </div>

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

        <div class="input-container">
            <select id="city" name="city" required>
                <option value="">--Select City--</option>
            </select>
        </div>

        <div class="input-container file-upload-container">
            <input type="file" id="fileInput" name="files[]" class="file-input" multiple>
            <label for="fileInput" class="file-upload-button">Choose Files</label>
        </div>

        <div class="input-container">
            <button type="submit" class="submit-button">Create Listing</button>
        </div>
    </form>

    <script src="dynamic_cities.js"></script>
    <script src="image_preview.js"></script>
</body>

<footer>
    <?php include 'footer.php'; ?>
</footer>

</html>
