<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Set current page for navigation highlighting
$current_page = 'user.php';

include 'includes/head.php';
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

  <title> Resto </title>

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

  <style>
    .dropdown-menu {
      background-color: rgba(0, 0, 0, 0.85);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .dropdown-item {
      color: white;
      padding: 8px 20px;
    }

    .dropdown-item:hover {
      background-color: rgba(255, 255, 255, 0.1);
      color: #ffbe33;
    }

    .dropdown-item i {
      margin-right: 10px;
      width: 20px;
    }

    .dropdown-divider {
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Notification styles */
    .notification-link {
        position: relative;
        display: inline-block;
        color: #ffffff;
        margin-right: 15px;
    }

    .notification-link:hover {
        color: #ffbe33;
    }

    .notification-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #ff4757;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    .notification-dropdown {
        width: 300px;
        padding: 0;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        border: none;
        max-height: 400px;
        overflow-y: auto;
    }

    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        background-color: #f8f9fa;
        border-radius: 8px 8px 0 0;
    }

    .notification-header a {
        color: #007bff;
        font-size: 12px;
    }

    .notification-item {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .notification-item:hover {
        background-color: #f8f9fa;
    }

    .notification-item.unread {
        background-color: #f0f7ff;
    }

    .notification-item .time {
        font-size: 11px;
        color: #999;
        margin-top: 5px;
    }

    .notification-empty {
        padding: 15px;
        text-align: center;
        color: #999;
    }

    .review_section {
        padding: 90px 0;
        background-color: #f8f9fa;
    }

    .review_form {
        max-width: 800px;
        margin: 0 auto 40px;
        padding: 30px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    .review_list {
        max-width: 800px;
        margin: 0 auto;
    }

    .review_item {
        background: white;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .review_item h5 {
        color: #ffbe33;
        margin-bottom: 10px;
    }

    .review_item p {
        color: #666;
        margin-bottom: 10px;
    }

    .review_date {
        font-size: 0.9em;
        color: #999;
    }

    .btn-primary {
        background-color: #ffbe33;
        border-color: #ffbe33;
        padding: 10px 25px;
    }

    .btn-primary:hover {
        background-color: #e6a82e;
        border-color: #e6a82e;
    }

    .form-control {
        border: 1px solid #ddd;
        padding: 12px;
        margin-bottom: 15px;
    }

    .heading_container {
        text-align: center;
        margin-bottom: 40px;
    }

    .heading_container h2 {
        color: #333;
        font-weight: bold;
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
        display: block;
    }

    .form-control {
        border: 2px solid #eee;
        border-radius: 8px;
        padding: 15px;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #ffbe33;
        box-shadow: 0 0 0 0.2rem rgba(255, 190, 51, 0.25);
    }

    select.form-control {
        height: 55px;
        background-color: white;
        cursor: pointer;
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .btn-submit {
        background-color: #ffbe33;
        color: white;
        padding: 15px 30px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        width: 100%;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        background-color: #e6a82e;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    /* Rating stars styling */
    .rating-stars {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .rating-option {
        background: white;
        border: 2px solid #eee;
        border-radius: 8px;
        padding: 10px 20px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .rating-option:hover {
        border-color: #ffbe33;
        background-color: #fff8e8;
    }

    .rating-option.selected {
        border-color: #ffbe33;
        background-color: #fff8e8;
    }

    /* Success message styling */
    .success-message {
        display: none;
        background-color: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
    }

    /* Star Rating styles */
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        font-size: 0;
    }
    
    .star-rating input {
        display: none;
    }
    
    .star-rating label {
        cursor: pointer;
        width: 40px;
        height: 40px;
        background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" fill="%23ddd"/></svg>');
        background-repeat: no-repeat;
        background-position: center;
        background-size: 36px;
    }
    
    .star-rating input:checked ~ label {
        background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" fill="%23ffbe33"/></svg>');
    }
    
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" fill="%23ffbe33"/></svg>');
    }
    
    .rating-text {
        color: #666;
        font-size: 14px;
        font-weight: 500;
        text-align: center;
    }

    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.2); }
      100% { transform: scale(1); }
    }

    .cart_link.pulse {
      animation: pulse 0.5s ease-in-out;
    }
  </style>

</head>

<body>

  <div class="hero_area">
    <div class="bg-box">
      <img src="images\indexpic.jpeg" alt="">
    </div>
    
    <?php include 'includes/user_header.php'; ?>
    
    <!-- slider section -->
    <section class="slider_section layout_padding-bottom">
      <div id="customCarousel1" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <div class="container ">
              <div class="row">
                <div class="col-md-7 col-lg-6 ">
                  <div class="detail-box">
                    <h1>
                      Fast Food Restaurant
                    </h1>
                    <p>
                      Welcome to Resto, your go-to destination for an unforgettable dining experience, whether you're visiting us in person, pre-ordering your favorite dishes, or enjoying our delicious meals delivered right to your doorstep. Our website offers a seamless and user-friendly experience, bringing our mouthwatering menu and exceptional service straight to you.
                    </p>
                    <div class="btn-box">
                      <a href="book.php" class="btn1">
                        Book Table 
                      </a>
                  
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="carousel-item ">
            <div class="container ">
              <div class="row">
                <div class="col-md-7 col-lg-6 ">
                  <div class="detail-box">
                    <h1>
                      Fast Food Restaurant
                    </h1>
                    <p>
                      Welcome to Resto, your go-to destination for an unforgettable dining experience, whether you're visiting us in person, pre-ordering your favorite dishes, or enjoying our delicious meals delivered right to your doorstep. Our website offers a seamless and user-friendly experience, bringing our mouthwatering menu and exceptional service straight to you.
                    </p>
                    <div class="btn-box">
                      <a href="menu.php" class="btn1">
                        Home Delivery
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="carousel-item">
            <div class="container ">
              <div class="row">
                <div class="col-md-7 col-lg-6 ">
                  <div class="detail-box">
                    <h1>
                      Fast Food Restaurant
                    </h1>
                    <p>
                      Welcome to Resto, your go-to destination for an unforgettable dining experience, whether you're visiting us in person, pre-ordering your favorite dishes, or enjoying our delicious meals delivered right to your doorstep. Our website offers a seamless and user-friendly experience, bringing our mouthwatering menu and exceptional service straight to you.
                    </p>
                    <div class="btn-box">
                      <a href="menu.php" class="btn1">
                       Pre-Order
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="container">
          <ol class="carousel-indicators">
            <li data-target="#customCarousel1" data-slide-to="0" class="active"></li>
            <li data-target="#customCarousel1" data-slide-to="1"></li>
            <li data-target="#customCarousel1" data-slide-to="2"></li>
          </ol>
        </div>
      </div>

    </section>
    <!-- end slider section -->
  </div>

  <!-- about section -->
  <section class="about_section layout_padding">
    <div class="container  ">
      <div class="row">
        <div class="col-md-6 ">
          <div class="img-box">
            <img src="images/about-img.png" alt="">
          </div>
        </div>
        <div class="col-md-6">
          <div class="detail-box">
            <div class="heading_container">
              <h2>
                We Are Resto
              </h2>
            </div>
            <p>
              There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration
              in some form, by injected humour, or randomised words which don't look even slightly believable. If you
              are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in
              the middle of text. All
            </p>
            <a href="">
              Read More
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- end about section -->

  <!-- Review section -->
  <section class="review_section layout_padding">
    <div class="container">
      <div class="heading_container heading_center mb-5">
        <h2>
          Share Your Experience
        </h2>
        <p class="text-center mt-3">
          We value your feedback! Let us know about your dining experience.
        </p>
      </div>
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="review_form">
            <div class="success-message" id="successMessage">
              <i class="fa fa-check-circle mr-2"></i> Thank you for your review!
            </div>
            <form id="reviewForm">
              <div class="form-group">
                <label for="rating">Your Rating</label>
                <div class="star-rating">
                  <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="5 stars"></label>
                  <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 stars"></label>
                  <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 stars"></label>
                  <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 stars"></label>
                  <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 star"></label>
                </div>
                <div class="rating-text mt-2" id="ratingText">Select your rating</div>
              </div>
              <div class="form-group">
                <label for="reviewText">Your Review</label>
                <textarea class="form-control" id="reviewText" rows="4" 
                  placeholder="Share your dining experience with us..." required></textarea>
              </div>
              <div class="text-center">
                <button type="submit" class="btn btn-submit">
                  <i class="fa fa-paper-plane mr-2"></i> Submit Review
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- end review section -->

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
              Feane
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
          &copy; <span id="displayYear"></span> All Rights Reserved
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
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCh39n5U-4IoWpsVGUHWdqB6puEkhRLdmI&callback=myMap">
  </script>
  <!-- End Google Map -->
  
  <script>
    $(document).ready(function() {
      console.log('Document ready, initializing carousel...');
      
      // Wait a short moment to ensure all reviews are loaded
      setTimeout(function() {
        // Check if we have any reviews
        var reviewCount = $('.client_owl-carousel .item').length;
        console.log('Number of review items found:', reviewCount);
        
        // Initialize Owl Carousel for reviews
        var $carousel = $(".client_owl-carousel");
        
        // Destroy existing carousel if it exists
        if ($carousel.hasClass('owl-loaded')) {
          $carousel.trigger('destroy.owl.carousel');
        }
        
        $carousel.on('initialized.owl.carousel', function(event) {
          console.log('Carousel initialized successfully');
          console.log('Active items:', event.item.count);
          // Force refresh of carousel position
          setTimeout(function() {
            $carousel.trigger('refresh.owl.carousel');
          }, 100);
        });
        
        $carousel.owlCarousel({
        loop: reviewCount > 1,
        margin: 20,
        dots: true,
        nav: false,
        autoplay: reviewCount > 1,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        responsive: {
          0: {
            items: 1
          },
          768: {
            items: reviewCount > 1 ? 2 : 1
          }
        }
      });
      
      // Add manual navigation if there are multiple reviews
      if (reviewCount > 1) {
        $('.client_section').append(
          '<div class="container mt-4">'+
            '<div class="text-center">'+
              '<button class="btn btn-primary mx-2 prev-review" style="background-color: #ffbe33; border-color: #ffbe33;">'+
                '<i class="fa fa-chevron-left"></i>'+
              '</button>'+
              '<button class="btn btn-primary mx-2 next-review" style="background-color: #ffbe33; border-color: #ffbe33;">'+
                '<i class="fa fa-chevron-right"></i>'+
              '</button>'+
            '</div>'+
          '</div>'
        );
        
        $('.next-review').click(function() {
          $carousel.trigger('next.owl.carousel');
        });
        
        $('.prev-review').click(function() {
          $carousel.trigger('prev.owl.carousel');
        });
      }
    });
  </script>

  <script>
    $(document).ready(function() {
        // Function to load notifications
        function loadNotifications() {
            console.log('Loading notifications...');
            $.ajax({
                url: 'get_notifications.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log('Notifications data:', data);
                    if (data.notifications && data.notifications.length > 0) {
                        let notificationHtml = '';
                        let unreadCount = 0;
                        
                        data.notifications.forEach(function(notification) {
                            console.log('Processing notification:', notification);
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
                    console.log('Response:', xhr.responseText);
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
        
        // Check for new table booking notifications specifically
        function checkTableBookingNotifications() {
            $.ajax({
                url: 'check_booking_notifications.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.hasNewNotifications) {
                        // Reload all notifications if there are new booking notifications
                        loadNotifications();
                        
                        // Optionally show a toast notification
                        if (data.message) {
                            showToastNotification(data.message);
                        }
                    }
                }
            });
        }
        
        // Function to show toast notification
        function showToastNotification(message) {
            // Create toast element if it doesn't exist
            if ($('#notification-toast').length === 0) {
                $('body').append(`
                    <div id="notification-toast" style="position: fixed; top: 20px; right: 20px; 
                        background-color: #ffbe33; color: #fff; padding: 15px; border-radius: 5px; 
                        box-shadow: 0 4px 8px rgba(0,0,0,0.1); z-index: 9999; display: none;">
                        <div id="toast-message"></div>
                    </div>
                `);
            }
            
            // Set message and show toast
            $('#toast-message').text(message);
            $('#notification-toast').fadeIn().delay(5000).fadeOut();
        }
        
        // Check for booking notifications every minute
        setInterval(checkTableBookingNotifications, 60000);
        
        // Also check once on page load
        checkTableBookingNotifications();
    });
  </script>

  <script>
    $(document).ready(function() {
      // Update rating text when user selects a rating
      $('.star-rating input').change(function() {
        const ratingValue = $(this).val();
        let ratingText = '';
        
        switch(ratingValue) {
          case '5':
            ratingText = 'Excellent (5 stars)';
            break;
          case '4':
            ratingText = 'Very Good (4 stars)';
            break;
          case '3':
            ratingText = 'Good (3 stars)';
            break;
          case '2':
            ratingText = 'Fair (2 stars)';
            break;
          case '1':
            ratingText = 'Poor (1 star)';
            break;
          default:
            ratingText = 'Select your rating';
        }
        
        $('#ratingText').text(ratingText);
      });
      
      // Handle review form submission
      $('#reviewForm').submit(function(e) {
        e.preventDefault();
        const rating = $('input[name="rating"]:checked').val();
        const review = $('#reviewText').val();
        
        if (!rating) {
          alert('Please select a rating');
          return;
        }
        
        $.ajax({
          url: 'submit_review.php',
          type: 'POST',
          data: {
            rating: rating,
            review: review
          },
          success: function(response) {
            try {
              const data = JSON.parse(response);
              if (data.success) {
                $('#successMessage').fadeIn();
                $('#reviewForm')[0].reset();
                $('#ratingText').text('Select your rating');
                
                setTimeout(function() {
                  $('#successMessage').fadeOut();
                }, 3000);
              } else {
                alert('Error submitting review: ' + data.error);
              }
            } catch(e) {
              console.error('Error parsing response:', e);
              alert('Error submitting review. Please try again.');
            }
          },
          error: function() {
            alert('Error submitting review. Please try again.');
          }
        });
      });
    });
  </script>

  <script>
    $(document).ready(function() {
      // Function to load cart count
      function loadCartCount() {
        $.ajax({
          url: 'get_cart_count.php',
          type: 'GET',
          dataType: 'json',
          success: function(data) {
            if (data.count > 0) {
              $('.cart-badge').text(data.count).show();
            } else {
              $('.cart-badge').hide();
            }
          },
          error: function(xhr, status, error) {
            console.error('Error loading cart count:', error);
          }
        });
      }
      
      // Load cart count when page loads
      loadCartCount();
      
      // Refresh cart count every 30 seconds
      setInterval(loadCartCount, 30000);
    });
  </script>

</body>
</html>