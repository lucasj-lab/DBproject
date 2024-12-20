<?php
session_start();
require 'database_connection.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Rookies</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- Header -->
    <header>
        <?php include 'header.php'; ?>
        </header>
    <script>
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById("mobileMenu");
            mobileMenu.classList.toggle("active");
        }
    </script>

    <!-- About Content -->
    <section class="about">
        <h2>Our Mission</h2>
        <p>Rookie-list 2.0 is a user-friendly platform to post and find classified ads with ease. Whether you're looking
            for housing, jobs, services, or things for sale, our website connects users quickly and efficiently.</p>

        <h2>Contact Us</h2>
        <p>For more information, feel free to reach out at <a href="mailto:support@rookie.com">support@rookie.com</a>.
        </p>
    </section>


    <footer>
        <?php include 'footer.php'; ?>
    </footer>

    <style>
        /* General Reset and Styles */


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
            width:fit-content;
        }

        .mobile-menu ul {
            display: flex;
            flex-direction: column;
            list-style: none;
            width:fit-content;
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