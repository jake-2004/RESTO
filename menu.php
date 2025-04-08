<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'database.php';
require_once 'get_menu_items.php';

// Check if user is logged in and address is added
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT address FROM Users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (empty($user['address'])) {
        $show_address_popup = true;
    }
}

// Get menu items and categories
$menu_items = getMenuItems();
$categories = getCategories();

// Debug information
error_log("Menu.php - Retrieved menu items: " . print_r($menu_items, true));
error_log("Menu.php - Retrieved categories: " . print_r($categories, true));

// Check database connection
if (!$conn->ping()) {
    die("Database connection failed");
}

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'MenuItems'");
if ($result->num_rows === 0) {
    die("MenuItems table does not exist");
}

// Set a flag to hide notifications on this page
$hide_notifications = true;

// Set current page for navigation highlighting
$current_page = 'menu.php';

include 'includes/head.php';
?>

<div class="hero_area" style="min-height: auto; padding: 0;">
    <?php include 'includes/user_header.php'; ?>
    
    <!-- food section -->
    <section class="food_section" style="padding-top: 20px;">
      <div class="container">
        <div class="heading_container heading_center">
          <h2 style="color: white; margin-top: 0;">Our Menu</h2>
        </div>

        <div class="filters-content">
          <div class="row">
            <?php if (empty($menu_items)): ?>
              <div class="col-12 text-center">
                <p>No menu items available. Please check back later.</p>
              </div>
            <?php else: ?>
              <?php 
              // Group menu items by category
              $items_by_category = [];
              foreach ($menu_items as $item) {
                  $category = $item['category'];
                  if (!isset($items_by_category[$category])) {
                      $items_by_category[$category] = [];
                  }
                  $items_by_category[$category][] = $item;
              }
              
              // Display items grouped by category
              foreach ($items_by_category as $category => $items): ?>
                <div class="col-12 mb-4">
                  <h3 class="category-title" style="color: #ffbe33; padding: 15px 0; border-bottom: 2px solid #ffbe33;">
                    <?php echo htmlspecialchars($category); ?>
                  </h3>
                  <div class="row">
                    <?php foreach ($items as $item): ?>
                      <div class="col-sm-6 col-lg-4 mb-4">
                        <div class="box">
                          <div>
                            <div class="img-box">
                              <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                                   alt="<?php echo htmlspecialchars($item['name']); ?>"
                                   class="menu-image">
                            </div>
                            <div class="detail-box">
                              <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                              <p class="description"><?php echo htmlspecialchars($item['description']); ?></p>
                              <div class="options">
                                <h6><?php echo number_format($item['price'], 2); ?></h6>
                                <a href="#" class="add-to-cart" data-item-id="<?php echo htmlspecialchars($item['menu_id']); ?>">
                                  <i class="fa fa-shopping-cart"></i>
                                </a>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
    <!-- end food section -->
</div>

<!-- footer section -->
<footer class="footer_section">
  <div class="container">
    <div class="row">
      <div class="col-md-4 footer-col">
        <div class="footer_contact">
          <h4>Contact Us</h4>
          <div class="contact_link_box">
            <a href="">
              <i class="fa fa-map-marker" aria-hidden="true"></i>
              <span>Location</span>
            </a>
            <a href="">
              <i class="fa fa-phone" aria-hidden="true"></i>
              <span>Call +01 1234567890</span>
            </a>
            <a href="">
              <i class="fa fa-envelope" aria-hidden="true"></i>
              <span>demo@gmail.com</span>
            </a>
          </div>
        </div>
      </div>
      <div class="col-md-4 footer-col">
        <div class="footer_detail">
          <a href="" class="footer-logo">Feane</a>
          <p>Necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with</p>
          <div class="footer_social">
            <a href=""><i class="fa fa-facebook" aria-hidden="true"></i></a>
            <a href=""><i class="fa fa-twitter" aria-hidden="true"></i></a>
            <a href=""><i class="fa fa-linkedin" aria-hidden="true"></i></a>
            <a href=""><i class="fa fa-instagram" aria-hidden="true"></i></a>
            <a href=""><i class="fa fa-pinterest" aria-hidden="true"></i></a>
          </div>
        </div>
      </div>
      <div class="col-md-4 footer-col">
        <h4>Opening Hours</h4>
        <p>Everyday</p>
        <p>10.00 Am -10.00 Pm</p>
      </div>
    </div>
    <div class="footer-info">
      <p>&copy; <span id="displayYear"></span> All Rights Reserved By
        <a href="https://html.design/">Free Html Templates</a>
      </p>
    </div>
  </div>
</footer>
<!-- end footer section -->

<?php if (isset($show_address_popup)): ?>
<!-- Address Modal -->
<div class="modal fade" id="addressModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addressModalLabel">Address Required</h5>
      </div>
      <div class="modal-body">
        <p>Please add your delivery address to your profile before ordering.</p>
      </div>
      <div class="modal-footer">
        <a href="userprofile.php" class="btn btn-primary">Go to Profile</a>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- jQery -->
<script src="js/jquery-3.4.1.min.js"></script>
<!-- popper js -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<!-- bootstrap js -->
<script src="js/bootstrap.js"></script>
<!-- owl slider -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<!-- isotope js -->
<script src="https://unpkg.com/isotope-layout@3.0.4/dist/isotope.pkgd.min.js"></script>
<!-- nice select -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js"></script>
<!-- custom js -->
<script src="js/custom.js"></script>

<script>
  $(document).ready(function() {
      <?php if (isset($show_address_popup)): ?>
      // Show address modal on page load
      $('#addressModal').modal('show');
      <?php endif; ?>

      // Make sure Bootstrap's dropdown functionality is properly initialized
      $('.dropdown-toggle').dropdown();

      // Add to cart functionality
      $('.add-to-cart').click(function(e) {
          e.preventDefault();
          
          <?php if (isset($show_address_popup)): ?>
          // Show modal if address is not added
          $('#addressModal').modal('show');
          return;
          <?php endif; ?>

          const itemId = $(this).data('item-id');
          const button = $(this);
          
          // Disable button to prevent double clicks
          button.prop('disabled', true);
          
          $.ajax({
              url: 'add_to_cart.php',
              method: 'POST',
              data: { menu_id: itemId },
              success: function(response) {
                  try {
                      if (typeof response === 'string') {
                          response = JSON.parse(response);
                      }
                      
                      if (response.success) {
                          // Refresh the page when item is successfully added to cart
                          location.reload();
                      } else {
                          alert(response.message || 'Error adding item to cart');
                      }
                  } catch (e) {
                      console.error('Error parsing response:', e);
                      alert('Error processing response');
                  }
              },
              error: function(xhr, status, error) {
                  console.error('Ajax error:', error);
                  alert('Error adding item to cart. Please try again.');
              },
              complete: function() {
                  // Re-enable button
                  button.prop('disabled', false);
              }
          });
      });
  });
</script>

<!-- Add this CSS for the pulse animation -->
<style>
  @keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
  }
  
  .pulse-animation {
    animation: pulse 0.5s ease-in-out;
  }
</style>
</body>
</html>
