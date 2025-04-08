<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set current page for navigation highlighting
$current_page = 'about.php';

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

  <title>Resto - About Us</title>

  <!-- bootstrap core css -->
  <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />

  <!--owl slider stylesheet -->
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
  <!-- nice select  -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/css/nice-select.min.css" integrity="sha512-CruCP+TD3yXzlvvijET8wV5WxxEh5H8P4cmz0RFbKK6FlZ2sYl3AEsKlLPHbniXKSrDdFewhbmBK5skbdsASbQ==" crossorigin="anonymous" />
  <!-- font awesome style -->
  <link href="css/font-awesome.min.css" rel="stylesheet" />

  <!-- Custom styles for this template -->
  <link href="css/style.css" rel="stylesheet" />
  <!-- responsive style -->
  <link href="css/responsive.css" rel="stylesheet" />
</head>

<body class="sub_page">
  <div class="hero_area" style="min-height: auto; padding: 0;">
    <?php 
    // Set a flag to hide notifications before including the header
    $hide_notifications = true;
    include 'includes/user_header.php'; 
    ?>
    
    <!-- about section -->
    <section class="about_section layout_padding">
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <div class="img-box">
              <img src="images/favicon.png" alt="">
            </div>
          </div>
          <div class="col-md-6">
            <div class="detail-box">
              <div class="heading_container">
                <h2 style="color: #ffbe33; font-weight: bold;">
                  We Are Resto
                </h2>
              </div>
              <p style="color: #ffffff; font-size: 1.1em; margin-bottom: 15px;">
                Welcome to Resto, your ultimate destination for a seamless dining experience, whether you're dining in, booking a table, or enjoying a delicious meal at home! At Resto, we're dedicated to bringing you the best in culinary delights with the convenience of modern technology.
              </p>
              <p style="color: #ffffff; font-size: 1.1em;">
                Founded in 2020, Resto has quickly become a favorite among food enthusiasts looking for quality cuisine and exceptional service. Our diverse menu features a perfect blend of traditional favorites and innovative creations, ensuring there's something for everyone.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- end about section -->

    <!-- our mission section -->
    <section class="about_section layout_padding" style="background-color: #222222;">
      <div class="container">
        <div class="heading_container heading_center">
          <h2 style="color: #ffbe33; font-weight: bold; margin-bottom: 30px;">Our Mission</h2>
        </div>
        <div class="row justify-content-center">
          <div class="col-md-8 text-center">
            <p style="color: #ffffff; font-size: 1.1em; margin-bottom: 15px;">
              At Resto, our mission is simple: to create memorable dining experiences through exceptional food, attentive service, and a welcoming atmosphere. We believe that a great meal has the power to bring people together and create lasting memories.
            </p>
            <p style="color: #ffffff; font-size: 1.1em;">
              We are committed to sourcing the freshest ingredients from local suppliers, ensuring that every dish that leaves our kitchen is of the highest quality. Our talented chefs put their heart and soul into every creation, combining traditional techniques with modern innovations.
            </p>
          </div>
        </div>
      </div>
    </section>
    <!-- end our mission section -->

    <!-- our story section -->
    <section class="about_section layout_padding">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <div class="detail-box">
              <div class="heading_container">
                <h2 style="color: #ffbe33; font-weight: bold;">Our Story</h2>
              </div>
              <p style="color: #ffffff; font-size: 1.1em; margin-bottom: 15px;">
                The journey of Resto began with a simple dream: to create a place where food lovers could indulge in extraordinary culinary creations while enjoying seamless service. What started as a small family-owned restaurant has now evolved into a beloved dining destination.
              </p>
              <p style="color: #ffffff; font-size: 1.1em;">
                Through years of dedication and passion, we've perfected our recipes and expanded our offerings. While our menu has grown, our commitment to quality and customer satisfaction remains unchanged. Each dish tells a story of tradition, innovation, and the joy of bringing people together through food.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- end our story section -->

    <!-- why choose us section -->
    <section class="about_section layout_padding" style="background-color: #222222;">
      <div class="container">
        <div class="heading_container heading_center">
          <h2 style="color: #ffbe33; font-weight: bold; margin-bottom: 30px;">Why Choose Us</h2>
        </div>
        <div class="row mt-5">
          <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm" style="background-color: #333333; border: 1px solid #ffbe33;">
              <div class="card-body text-center">
                <i class="fa fa-cutlery fa-3x mb-3" style="color: #ffbe33;"></i>
                <h4 class="card-title" style="color: #ffbe33; font-weight: bold;">Quality Cuisine</h4>
                <p class="card-text" style="color: #ffffff;">We use only the freshest ingredients to create our delicious dishes, ensuring a memorable dining experience every time.</p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm" style="background-color: #333333; border: 1px solid #ffbe33;">
              <div class="card-body text-center">
                <i class="fa fa-clock-o fa-3x mb-3" style="color: #ffbe33;"></i>
                <h4 class="card-title" style="color: #ffbe33; font-weight: bold;">Fast Service</h4>
                <p class="card-text" style="color: #ffffff;">Our efficient ordering system and dedicated staff ensure that your food is prepared and delivered promptly.</p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm" style="background-color: #333333; border: 1px solid #ffbe33;">
              <div class="card-body text-center">
                <i class="fa fa-mobile fa-3x mb-3" style="color: #ffbe33;"></i>
                <h4 class="card-title" style="color: #ffbe33; font-weight: bold;">Easy Ordering</h4>
                <p class="card-text" style="color: #ffffff;">With our online platform, ordering your favorite meals for delivery or pickup has never been easier.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- end why choose us section -->

    <!-- meet our team section -->
    <section class="about_section layout_padding">
      <div class="container">
        <div class="heading_container heading_center">
          <h2 style="color: #ffbe33; font-weight: bold; margin-bottom: 30px;">Meet Our Team</h2>
        </div>
        <div class="row mt-5">
          <div class="col-md-4 mb-4">
            <div class="card team-card shadow-sm" style="background-color: #333333; border: 1px solid #ffbe33;">
              <img src="images/chef1.jpg" class="card-img-top" alt="Executive Chef">
              <div class="card-body text-center">
                <h5 class="card-title" style="color: #ffbe33; font-weight: bold;">John Doe</h5>
                <p class="text-muted" style="color: #ffffff !important;">Executive Chef</p>
                <p class="card-text" style="color: #ffffff;">With over 15 years of culinary experience, Chef John brings creativity and passion to every dish he creates.</p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card team-card shadow-sm" style="background-color: #333333; border: 1px solid #ffbe33;">
              <img src="images/chef2.jpg" class="card-img-top" alt="Pastry Chef">
              <div class="card-body text-center">
                <h5 class="card-title" style="color: #ffbe33; font-weight: bold;">Jane Smith</h5>
                <p class="text-muted" style="color: #ffffff !important;">Pastry Chef</p>
                <p class="card-text" style="color: #ffffff;">Chef Jane is renowned for her exquisite desserts that provide the perfect sweet ending to any meal.</p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card team-card shadow-sm" style="background-color: #333333; border: 1px solid #ffbe33;">
              <img src="images/manager.jpg" class="card-img-top" alt="Restaurant Manager">
              <div class="card-body text-center">
                <h5 class="card-title" style="color: #ffbe33; font-weight: bold;">David Wilson</h5>
                <p class="text-muted" style="color: #ffffff !important;">Restaurant Manager</p>
                <p class="card-text" style="color: #ffffff;">David ensures that every aspect of your dining experience meets our high standards of excellence.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- end meet our team section -->
  </div>

  <!-- footer section -->
  <footer class="footer_section">
    <div class="container">
      <div class="row">
        <div class="col-md-4 footer-col">
          <div class="footer_contact">
            <h4>
              Contact Us
            </h4>
            <div class="contact_link_box">
              <a href="">
                <i class="fa fa-map-marker" aria-hidden="true"></i>
                <span>
                  Location
                </span>
              </a>
              <a href="">
                <i class="fa fa-phone" aria-hidden="true"></i>
                <span>
                  Call +01 1234567890
                </span>
              </a>
              <a href="">
                <i class="fa fa-envelope" aria-hidden="true"></i>
                <span>
                  demo@gmail.com
                </span>
              </a>
            </div>
          </div>
        </div>
        <div class="col-md-4 footer-col">
          <div class="footer_detail">
            <a href="" class="footer-logo">
              Resto
            </a>
            <p>
              Necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with
            </p>
            <div class="footer_social">
              <a href="">
                <i class="fa fa-facebook" aria-hidden="true"></i>
              </a>
              <a href="">
                <i class="fa fa-twitter" aria-hidden="true"></i>
              </a>
              <a href="">
                <i class="fa fa-linkedin" aria-hidden="true"></i>
              </a>
              <a href="">
                <i class="fa fa-instagram" aria-hidden="true"></i>
              </a>
              <a href="">
                <i class="fa fa-pinterest" aria-hidden="true"></i>
              </a>
            </div>
          </div>
        </div>
        <div class="col-md-4 footer-col">
          <h4>
            Opening Hours
          </h4>
          <p>
            Everyday
          </p>
          <p>
            10.00 Am -10.00 Pm
          </p>
        </div>
      </div>
      <div class="footer-info">
        <p>
          &copy; <span id="displayYear"></span> All Rights Reserved By
          <a href="https://html.design/">Free Html Templates</a><br><br>
          &copy; <span id="displayYear"></span> Distributed By
          <a href="https://themewagon.com/" target="_blank">ThemeWagon</a>
        </p>
      </div>
    </div>
  </footer>
  <!-- footer section -->

  <!-- jQery -->
  <script src="js/jquery-3.4.1.min.js"></script>
  <!-- popper js -->
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
  </script>
  <!-- bootstrap js -->
  <script src="js/bootstrap.js"></script>
  <!-- owl slider -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js">
  </script>
  <!-- isotope js -->
  <script src="https://unpkg.com/isotope-layout@3.0.4/dist/isotope.pkgd.min.js"></script>
  <!-- nice select -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js"></script>
  <!-- custom js -->
  <script src="js/custom.js"></script>
  <!-- Google Map -->
  <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY" async defer></script>
  
  <script>
    $(document).ready(function() {
        // Initialize dropdown
        $('.dropdown-toggle').dropdown();
    });
  </script>

  <script>
    $(document).ready(function() {
      // Function to load notifications
      function loadNotifications() {
        $.ajax({
          url: 'get_notifications.php',
          type: 'GET',
          dataType: 'json',
          success: function(data) {
            if (data.notifications && data.notifications.length > 0) {
              let notificationHtml = '';
              let unreadCount = 0;
              
              data.notifications.forEach(function(notification) {
                if (notification.is_read == 0) {
                  unreadCount++;
                }
                
                notificationHtml += `
                  <div class="notification-item ${notification.is_read == 0 ? 'unread' : ''}" 
                       data-id="${notification.id}">
                    <div>${notification.message}</div>
                    <div class="time">${timeAgo(new Date(notification.created_at))}</div>
                  </div>
                `;
              });
              
              $('#notification-list').html(notificationHtml);
              
              if (unreadCount > 0) {
                $('#notification-count').text(unreadCount).show();
              } else {
                $('#notification-count').hide();
              }
            } else {
              $('#notification-list').html('<div class="notification-empty">No notifications</div>');
              $('#notification-count').hide();
            }
          },
          error: function(xhr, status, error) {
            console.error('Error loading notifications:', error);
            $('#notification-list').html('<div class="notification-empty">Error loading notifications</div>');
          }
        });
      }
      
      // Function to format time ago
      function timeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        
        let interval = Math.floor(seconds / 31536000);
        if (interval > 1) return interval + ' years ago';
        if (interval === 1) return '1 year ago';
        
        interval = Math.floor(seconds / 2592000);
        if (interval > 1) return interval + ' months ago';
        if (interval === 1) return '1 month ago';
        
        interval = Math.floor(seconds / 86400);
        if (interval > 1) return interval + ' days ago';
        if (interval === 1) return '1 day ago';
        
        interval = Math.floor(seconds / 3600);
        if (interval > 1) return interval + ' hours ago';
        if (interval === 1) return '1 hour ago';
        
        interval = Math.floor(seconds / 60);
        if (interval > 1) return interval + ' minutes ago';
        if (interval === 1) return '1 minute ago';
        
        if (seconds < 10) return 'just now';
        
        return Math.floor(seconds) + ' seconds ago';
      }
      
      // Click handler for marking notifications as read
      $(document).on('click', '.notification-item', function() {
        const id = $(this).data('id');
        $.ajax({
          url: 'mark_notification_read.php',
          type: 'POST',
          data: { notification_id: id },
          dataType: 'json',
          success: function() {
            loadNotifications();
          }
        });
      });
      
      // Click handler for marking all notifications as read
      $('#mark-all-read').click(function(e) {
        e.preventDefault();
        $.ajax({
          url: 'mark_all_notifications_read.php',
          type: 'POST',
          dataType: 'json',
          success: function() {
            loadNotifications();
          }
        });
      });
      
      // Load notifications when page loads
      loadNotifications();
      
      // Refresh notifications every 30 seconds
      setInterval(loadNotifications, 30000);
    });
  </script>
</body>
</html>
