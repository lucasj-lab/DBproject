<?php
require 'database_connection.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rookielist</title>
  <link rel="stylesheet" href="styles.css">
</head>
<?php include 'header.php'; ?>
<body>


  <main>
    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-content">
        <div class="content-group">
          <h2>Find Anything</h2>
          <p>Join us today! <a href="signup.php">Sign up</a> to start posting your listings.</p>
        </div>
      </div>
    </section>
    
<!-- Search Form -->
<form action="search_listings.php" method="get" class="home-search-form">
  <div class="home-search-container">
    <input type="text" id="q" name="q" placeholder="Search" class="home-search-input" required>
    <button type="submit" class="home-search-btn">
      <label>Search</label>
    </button>
  </div>
</form>

    <!-- Categories Section -->
    <section class="browse-categories">
      <h2>Browse Categories</h2>
      <div class="category-grid">
        <!-- Category Forms -->
        <form action="browse_category.php" method="GET" class="category-item">
          <input type="hidden" name="category" value="Auto">
          <button type="submit" class="home-pill-buttons">
            <img src="images/auto_image.jpg" alt="Auto Category" class="category-image">
            Auto
          </button>
        </form>

        <form action="browse_category.php" method="GET" class="category-item">
          <input type="hidden" name="category" value="Electronics">
          <button type="submit" class="home-pill-buttons">
            <img src="images/electronics_image.jpg" alt="Electronics Category" class="category-image">
            Electronics
          </button>
        </form>

        <form action="browse_category.php" method="GET" class="category-item">
          <input type="hidden" name="category" value="Furniture">
          <button type="submit" class="home-pill-buttons">
            <img src="images/furniture_image.jpg" alt="Furniture Category" class="category-image">
            Furniture
          </button>
        </form>

        <form action="browse_category.php" method="GET" class="category-item">
          <input type="hidden" name="category" value="Other">
          <button type="submit" class="home-pill-buttons">
            <img src="images/other_image.jpg" alt="Other Category" class="category-image">
            Other
          </button>
        </form>
      </div>
    </section>
  </main>

  <script>
    function toggleMobileMenu() {
      document.getElementById("mobileMenu").classList.toggle("active");
    }
  </script>

  <footer>
    <?php include 'footer.php'; ?>
  </footer>
</body>

</html>