<?php
require 'database_connection.php';

session_start();

?>

<!DOCTYPE html>
<html lang="en">

<body>
  <?php include 'header.php'; ?>

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

    <form action="search_listings.php" method="get" class="search-form">
      <div class="search-container">
        <input type="text" id="title" name="title" placeholder="Search" class="search-input" required>
        <button type="submit" class="search-btn">
          <light>Search</light>
        </button>
      </div>
    </form>

    <!-- Categories Section -->
    <section class="browse-categories">
      <h2>Browse Categories</h2>
      <div class="category-grid">

        <form action="browse_category.php" method="GET" class="category-item">
          <input type="hidden" name="category" value="Auto">
          <button type="submit" class="pill-button">
            <img src="images/auto_image.jpg" alt="Auto Category" class="category-image">
            Auto
          </button>
        </form>

        <form action="browse_category.php" method="GET" class="category-item">
          <input type="hidden" name="category" value="Electronics">
          <button type="submit" class="pill-button">
            <img src="images/electronics_image.jpg" alt="Electronics Category" class="category-image">
            Electronics
          </button>
        </form>

        <form action="browse_category.php" method="GET" class="category-item">
          <input type="hidden" name="category" value="Furniture">
          <button type="submit" class="pill-button">
            <img src="images/furniture_image.jpg" alt="Furniture Category" class="category-image">
            Furniture
          </button>
        </form>

        <form action="browse_category.php" method="GET" class="category-item">
          <input type="hidden" name="category" value="Other">
          <button type="submit" class="pill-button">
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