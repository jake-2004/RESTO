<?php
// Only start session if one hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';
?>

<style>
  .header_section {
    background-color: #222831;
    padding: 15px 0;
  }

  .custom_nav-container {
    padding: 0;
  }

  .navbar-brand {
    color: #ffffff;
    font-weight: bold;
  }

  .navbar-brand span {
    font-size: 24px;
  }

  .custom_nav-container .navbar-nav .nav-item .nav-link {
    padding: 5px 20px;
    color: #ffffff;
    text-align: center;
    text-transform: uppercase;
    border-radius: 5px;
    transition: all 0.3s;
  }

  .custom_nav-container .navbar-nav .nav-item:hover .nav-link, 
  .custom_nav-container .navbar-nav .nav-item.active .nav-link {
    color: #ffbe33;
  }

  .user_option {
    display: flex;
    align-items: center;
  }

  .user_option a {
    color: #ffffff;
    margin: 0 10px;
  }

  .user_option a:hover {
    color: #ffbe33;
  }

  .cart_link svg {
    width: 20px;
    height: auto;
    fill: #ffffff;
  }

  .cart_link:hover svg {
    fill: #ffbe33;
  }

  /* Dropdown styles */
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

  /* Badge styles */
  .notification-badge, .cart-badge {
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

  /* Position relative for badge containers */
  .notification-link, .cart_link {
    position: relative;
    display: inline-block;
  }

  @media (max-width: 991.98px) {
    .custom_nav-container .navbar-nav {
      padding-top: 15px;
      align-items: center;
    }

    .user_option {
      justify-content: center;
      margin-top: 15px;
    }
  }
</style>

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
          <li class="nav-item <?php echo (!isset($current_page) || $current_page == 'user.php') ? 'active' : ''; ?>">
            <a class="nav-link" href="user.php">Home <?php echo (!isset($current_page) || $current_page == 'user.php') ? '<span class="sr-only">(current)</span>' : ''; ?></a>
          </li>
          <li class="nav-item <?php echo (isset($current_page) && $current_page == 'menu.php') ? 'active' : ''; ?>">
            <a class="nav-link" href="menu.php">Menu <?php echo (isset($current_page) && $current_page == 'menu.php') ? '<span class="sr-only">(current)</span>' : ''; ?></a>
          </li>
          <li class="nav-item <?php echo (isset($current_page) && $current_page == 'about.php') ? 'active' : ''; ?>">
            <a class="nav-link" href="about.php">About <?php echo (isset($current_page) && $current_page == 'about.php') ? '<span class="sr-only">(current)</span>' : ''; ?></a>
          </li>
          <li class="nav-item <?php echo (isset($current_page) && $current_page == 'book.php') ? 'active' : ''; ?>">
            <a class="nav-link" href="book.php">Book Table <?php echo (isset($current_page) && $current_page == 'book.php') ? '<span class="sr-only">(current)</span>' : ''; ?></a>
          </li>
        </ul>
        
        <div class="user_option">
          <?php if (!isset($hide_notifications)): ?>
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
          <?php endif; ?>
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
              <a class="dropdown-item" href="user_booking_status.php">
                <i class="fa fa-calendar" aria-hidden="true"></i> My Reservations
              </a>
              
              <a class="dropdown-item" href="logout.php">
                <i class="fa fa-sign-out" aria-hidden="true"></i> Logout
              </a>
            </div>
          </div>
          <a class="cart_link" href="cart.php">
            <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 456.029 456.029" style="enable-background:new 0 0 456.029 456.029;" xml:space="preserve">
              <g><g><path d="M345.6,338.862c-29.184,0-53.248,23.552-53.248,53.248c0,29.184,23.552,53.248,53.248,53.248
               c29.184,0,53.248-23.552,53.248-53.248C398.336,362.926,374.784,338.862,345.6,338.862z" /></g></g>
              <g><g><path d="M439.296,84.91c-1.024,0-2.56-0.512-4.096-0.512H112.64l-5.12-34.304C104.448,27.566,84.992,10.67,61.952,10.67H20.48
               C9.216,10.67,0,19.886,0,31.15c0,11.264,9.216,20.48,20.48,20.48h41.472c2.56,0,4.608,2.048,5.12,4.608l31.744,216.064
               c4.096,27.136,27.648,47.616,55.296,47.616h212.992c26.624,0,49.664-18.944,55.296-45.056l33.28-166.4
               C457.728,97.71,450.56,86.958,439.296,84.91z" /></g></g>
              <g><g><path d="M215.04,389.55c-1.024-28.16-24.576-50.688-52.736-50.688c-29.696,1.536-52.224,26.112-51.2,55.296
               c1.024,28.16,24.064,50.688,52.224,50.688h1.024C193.536,443.31,216.576,418.734,215.04,389.55z" /></g></g>
            </svg>
            <?php
            // Get cart count
            $cart_count_query = "SELECT SUM(quantity) as count FROM Cart WHERE user_id = ?";
            $stmt = $conn->prepare($cart_count_query);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $cart_count = $result->fetch_assoc()['count'];
            
            if ($cart_count > 0): 
            ?>
                <span class="cart-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
          </a>
        </div>
      </div>
    </nav>
  </div>
</header> 