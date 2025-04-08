<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html>

<head>
  <!-- Basic -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <!-- Site Metas -->
  <meta name="keywords" content="" />
  <meta name="description" content="" />
  <meta name="author" content="" />
  <link rel="shortcut icon" href="images/favicon.png" type="">

  <title>Resto - Welcome</title>

  <?php include 'includes/header.php'; ?>
</head>

<body>
  <div class="hero_area">
    <div class="bg-box">
      <img src="images/indexpic.jpeg" alt="">
    </div>
    <?php include 'includes/navbar.php'; ?>

    <!-- slider section -->
    <section class="slider_section">
      <div id="customCarousel1" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <div class="container">
              <div class="row">
                <div class="col-md-7 col-lg-6">
                  <div class="detail-box">
                    <h1>Welcome to Resto</h1>
                    <p>Experience the finest dining with our carefully curated menu and exceptional service.</p>
                    <div class="btn-box">
                      <a href="menu.php" class="btn1">View Menu</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- end slider section -->
  </div>

  <?php include 'includes/footer.php'; ?>

  <script>
    $(document).ready(function() {
        // Initialize dropdown
        $('.dropdown-toggle').dropdown();
    });
  </script>
</body>
</html>
