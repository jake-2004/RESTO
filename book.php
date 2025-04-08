<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Set current page for navigation highlighting
$current_page = 'book.php';
// Set flag to hide notifications
$hide_notifications = true;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        $required_fields = ['name', 'phone', 'email', 'num_persons', 'booking_date', 'booking_time'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("All fields are required");
            }
        }

        // Validate email
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Validate phone (simple validation)
        if (!preg_match("/^[0-9]{10}$/", $_POST['phone'])) {
            throw new Exception("Invalid phone number format");
        }

        // Validate number of persons
        if (!is_numeric($_POST['num_persons']) || $_POST['num_persons'] < 1 || $_POST['num_persons'] > 10) {
            throw new Exception("Number of persons must be between 1 and 10");
        }

        // Validate booking date and time
        $booking_date = date('Y-m-d', strtotime($_POST['booking_date']));
        $booking_time = date('H:i:s', strtotime($_POST['booking_time']));
        
        if (strtotime($booking_date) < strtotime('today')) {
            throw new Exception("Booking date cannot be in the past");
        }

        // Add validation for booking time when date is today
        if ($booking_date == date('Y-m-d') && strtotime($booking_time) < time()) {
            throw new Exception("Booking time cannot be in the past");
        }

        // Add validation for restaurant hours (10:00 AM - 10:00 PM)
        $hour = (int)date('H', strtotime($booking_time));
        if ($hour < 10 || $hour >= 22) {
            throw new Exception("Booking time must be between 10:00 AM and 10:00 PM");
        }

        // Insert booking
        $query = "INSERT INTO TableBookings (user_id, name, phone, email, num_persons, booking_date, booking_time) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isssiss", 
            $user_id,
            $_POST['name'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['num_persons'],
            $booking_date,
            $booking_time
        );

        if ($stmt->execute()) {
            $success_message = "Table booked successfully! We will confirm your booking soon.";
        } else {
            throw new Exception("Error booking table");
        }

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Get user details for pre-filling the form
try {
    $query = "SELECT name, email, phone FROM Users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_details = $stmt->get_result()->fetch_assoc();
} catch (Exception $e) {
    error_log("Error fetching user details: " . $e->getMessage());
    $user_details = ['name' => '', 'email' => '', 'phone' => ''];
}

include 'includes/head.php';
?>

<div class="hero_area" style="min-height: auto; padding: 0;">
    <?php include 'includes/user_header.php'; ?>
    
    <!-- book section -->
    <section class="book_section layout_padding">
      <div class="container">
        <div class="heading_container text-center mb-5">
          <h2>Book A Table</h2>
        </div>

        <?php if ($success_message): ?>
          <div class="alert alert-success">
              <?php echo htmlspecialchars($success_message); ?>
          </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
          <div class="alert alert-danger">
              <?php echo htmlspecialchars($error_message); ?>
          </div>
        <?php endif; ?>

        <div class="row">
          <div class="col-md-8 mx-auto">
            <div class="form_container">
              <form method="POST" action="book.php">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <input type="text" class="form-control" name="name" placeholder="Your Name" 
                             value="<?php echo htmlspecialchars($user_details['name']); ?>" required />
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <input type="tel" class="form-control" name="phone" placeholder="Phone Number (10 digits)" 
                             value="<?php echo htmlspecialchars($user_details['phone']); ?>" 
                             pattern="[0-9]{10}" required />
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <input type="email" class="form-control" name="email" placeholder="Your Email" 
                             value="<?php echo htmlspecialchars($user_details['email']); ?>" required />
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <select class="form-control" name="num_persons" required>
                        <option value="" disabled selected>How many persons?</option>
                        <option value="1">1 person</option>
                        <option value="2">2 persons</option>
                        <option value="3">3 persons</option>
                        <option value="4">4 persons</option>
                        <option value="5">5 persons</option>
                        <option value="6">6 persons</option>
                        <option value="7">7 persons</option>
                        <option value="8">8 persons</option>
                        <option value="9">9 persons</option>
                        <option value="10">10 persons</option>
                      </select>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <input type="date" class="form-control" name="booking_date" 
                             min="<?php echo date('Y-m-d'); ?>" required>
                      <small class="text-muted">Select a date</small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <input type="time" class="form-control" name="booking_time" 
                             min="10:00" max="22:00" required>
                      <small class="text-muted">Restaurant hours: 10:00 AM - 10:00 PM</small>
                    </div>
                  </div>
                </div>
                
                <div class="text-center mt-4">
                  <div class="btn_box">
                    <button type="submit">Book Now</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- end book section -->
</div>

<?php include 'includes/footer.php'; ?>

<!-- jQuery -->
<script src="js/jquery-3.4.1.min.js"></script>
<!-- Bootstrap js -->
<script src="js/bootstrap.js"></script>
<!-- Nice Select -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js"></script>
<!-- Custom js -->
<script src="js/custom.js"></script>

<script>
  $(document).ready(function() {
      // Clear any user.php navigation flags since we're now in book.php
      sessionStorage.removeItem('fromUserPage');
      sessionStorage.removeItem('userPageSession');

      // DO NOT initialize nice-select for the select elements
      // $('select').niceSelect();

      // Initialize dropdown
      $('.dropdown-toggle').dropdown();

      // Set min time based on selected date
      $('input[name="booking_date"]').change(function() {
          const selectedDate = new Date($(this).val());
          const today = new Date();
          
          if (selectedDate.toDateString() === today.toDateString()) {
              const currentHour = today.getHours();
              const currentMinutes = today.getMinutes();
              const minTime = `${String(currentHour).padStart(2, '0')}:${String(currentMinutes).padStart(2, '0')}`;
              $('input[name="booking_time"]').attr('min', minTime);
          } else {
              $('input[name="booking_time"]').attr('min', '10:00');
          }
      });
  });
</script>
</body>
</html>
