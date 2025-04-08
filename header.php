<?php
// Only start session if one doesn't already exist
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
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
  </style>
</head>

<body>
  <div class="hero_area">
    <div class="bg-box">
      <img src="images\indexpic.jpeg" alt="">
    </div>
    <!-- header section strats -->
    <header class="header_section">
      <div class="container">
        <nav class="navbar navbar-expand-lg custom_nav-container ">
          <a class="navbar-brand" href="index.php">
            <span>
              Resto
            </span>
          </a>

          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class=""> </span>
          </button>

          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav  mx-auto ">
              <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'user.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="user.php">Home <?php echo basename($_SERVER['PHP_SELF']) == 'user.php' ? '<span class="sr-only">(current)</span>' : ''; ?></a>
              </li>
              <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'menu.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="menu.php">Menu <?php echo basename($_SERVER['PHP_SELF']) == 'menu.php' ? '<span class="sr-only">(current)</span>' : ''; ?></a>
              </li>
              <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="about.php">About <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? '<span class="sr-only">(current)</span>' : ''; ?></a>
              </li>
              <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'book.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="book.php">Book Table <?php echo basename($_SERVER['PHP_SELF']) == 'book.php' ? '<span class="sr-only">(current)</span>' : ''; ?></a>
              </li>
            </ul>
            
            <div class="user_option">
              <div class="dropdown">
                <a href="#" class="notification-link dropdown-toggle" id="notificationDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fa fa-bell" aria-hidden="true"></i>
                  <span class="notification-badge" id="notification-count" style="display: none;">0</span>
                </a>
                <div class="dropdown-menu notification-dropdown" aria-labelledby="notificationDropdown">
                  <div class="notification-header">
                    <span><strong>Notifications</strong></span>
                    <a href="#" id="mark-all-read">Mark all as read</a>
                  </div>
                  <div id="notification-list">
                    <div class="notification-empty">Loading notifications...</div>
                  </div>
                </div>
              </div>
              <div class="dropdown">
                <a href="#" class="user_link dropdown-toggle" id="userDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fa fa-user" aria-hidden="true"></i>
                </a>
                <div class="dropdown-menu" aria-labelledby="userDropdown">
                  <a class="dropdown-item" href="userprofile.php">
                    <i class="fa fa-user-circle" aria-hidden="true"></i> Profile
                  </a>
                  <a class="dropdown-item" href="orders.php">
                    <i class="fa fa-shopping-bag" aria-hidden="true"></i> My Orders
                  </a>
                  
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" href="settings.php">
                    <i class="fa fa-cog" aria-hidden="true"></i> Settings
                  </a>
                  <a class="dropdown-item" href="logout.php">
                    <i class="fa fa-sign-out" aria-hidden="true"></i> Logout
                  </a>
                </div>
              </div>
              <a class="cart_link" href="cart.php">
                <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 456.029 456.029" style="enable-background:new 0 0 456.029 456.029;" xml:space="preserve">
                  <g>
                    <g>
                      <path d="M345.6,338.862c-29.184,0-53.248,23.552-53.248,53.248c0,29.184,23.552,53.248,53.248,53.248
                   c29.184,0,53.248-23.552,53.248-53.248C398.336,362.926,374.784,338.862,345.6,338.862z" />
                    </g>
                  </g>
                  <g>
                    <g>
                      <path d="M439.296,84.91c-1.024,0-2.56-0.512-4.096-0.512H112.64l-5.12-34.304C104.448,27.566,84.992,10.67,61.952,10.67H20.48
                   C9.216,10.67,0,19.886,0,31.15c0,11.264,9.216,20.48,20.48,20.48h41.472c2.56,0,4.608,2.048,5.12,4.608l31.744,216.064
                   c4.096,27.136,27.648,47.616,55.296,47.616h212.992c26.624,0,49.664-18.944,55.296-45.056l33.28-166.4
                   C457.728,97.71,450.56,86.958,439.296,84.91z" />
                    </g>
                  </g>
                  <g>
                    <g>
                      <path d="M215.04,389.55c-1.024-28.16-24.576-50.688-52.736-50.688c-29.696,1.536-52.224,26.112-51.2,55.296
                   c1.024,28.16,24.064,50.688,52.224,50.688h1.024C193.536,443.31,216.576,418.734,215.04,389.55z" />
                    </g>
                  </g>
                </svg>
              </a>
            </div>
          </div>
        </nav>
      </div>
    </header>
    <!-- end header section -->