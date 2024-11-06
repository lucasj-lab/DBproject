
<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rookielist</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOMt23cez/3paNdF+K9aIIXUXl09Aq5AxlE9+y5T" crossorigin="anonymous">

  <!-- External CSS file -->
</head>

<body>

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

  <!-- Footer Section -->
  <footer>
    <p>&copy; 2024 Rookies 2.0 | All rights reserved.</p>
    <div class="footer-links">
      <a href="#">Privacy Policy</a>
      <a href="#">Terms of Service</a>
    </div>
  </footer>

  <script>
    function toggleMobileMenu() {
      document.getElementById("mobileMenu").classList.toggle("active");
    }
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
      background-color: #ffffff;
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
      width:fit-content;
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