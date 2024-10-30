<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Edit Listing</h1>
    </header>

    <?php include 'edit_listing_logic.php'; ?>

    <div class="edit-listing">
        <?php if (isset($error_message)) : ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="title">Title:</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" required>

            <label for="description">Description:</label>
            <textarea name="description" required><?php echo htmlspecialchars($description); ?></textarea>

            <label for="price">Price:</label>
            <input type="number" name="price" value="<?php echo htmlspecialchars($price); ?>" required>

            <button type="submit">Update Listing</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2024 Craigslist 2.0 | All rights reserved</p>
    </footer>
</body>
</html>
