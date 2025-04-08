<?php
require_once 'config/database.php';
require_once 'check_staff_session.php';

// Fetch all reviews with user information
$query = "SELECT r.*, u.user_name, u.email 
          FROM reviews r 
          LEFT JOIN users u ON r.user_id = u.user_id 
          ORDER BY r.created_at DESC";
$result = $conn->query($query);
$reviews = [];
if ($result) {
    $reviews = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Resto Staff Panel - Review Management">
    <meta name="author" content="Resto">
    <meta name="keywords" content="restaurant, staff, management, reviews">
    
    <!-- Prevent caching -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="images/favicon.png" type="">
    
    <!-- Page Title -->
    <title>Resto Staff - Review Management</title>

    <!-- Core CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">

    <!-- Custom Staff Styles -->
    <style>
        /* Inherit base styles from staff.php */
        .hero_area {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .bg-box {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .bg-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.4);
        }

        /* Header & Navigation */
        .header_section {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            position: relative;
            z-index: 1;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Modified color scheme for staff */
        :root {
            --staff-primary: #4CAF50;
            --staff-secondary: #45a049;
            --staff-accent: #81c784;
        }

        /* Staff content area - Enhanced */
        .staff-content {
            flex: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            color: white;
            padding: 40px;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(5px);
        }

        /* Review card styling */
        .reviews-list {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .review-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .review-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .review-user i {
            font-size: 2.5em;
            color: #ffbe33;
        }
        
        .review-user-info h5 {
            margin: 0;
            color: #ffbe33;
        }
        
        .review-user-info p {
            margin: 0;
            font-size: 0.9em;
            color: #bdbdbd;
        }
        
        .review-rating {
            display: flex;
            gap: 5px;
        }
        
        .review-rating i {
            color: #ffc107;
        }
        
        .review-content {
            margin: 15px 0;
            color: #e0e0e0;
            line-height: 1.6;
        }
        
        .review-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            font-size: 0.9em;
            color: #bdbdbd;
        }
        
        .review-date {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Page title styling */
        .page-title {
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 16px;
            border-left: 5px solid var(--staff-primary);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .page-title h2 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            color: var(--staff-primary);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .page-title p {
            margin: 10px 0 0;
            font-size: 1.1rem;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="hero_area">
        <!-- Background Image -->
        <div class="bg-box">
            <img src="images/indexpic.jpeg" alt="Restaurant Background">
        </div>

        <!-- Include the staff header -->
        <?php include 'staff-header.php'; ?>

        <!-- Display messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="container mt-3">
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <?php 
            // Clear the message after displaying
            unset($_SESSION['message']); 
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>
       
        <!-- Main Content Section -->
        <div class="staff-content">
            <div class="container">
                <!-- Page Title -->
                <div class="page-title">
                    <h2>Review Management</h2>
                    <p>View and manage customer reviews for your restaurant</p>
                </div>
                
                <!-- Reviews List -->
                <div class="reviews-list">
                    <?php if (empty($reviews)): ?>
                        <div class="alert alert-info" role="alert">
                            <i class="fa fa-info-circle mr-2"></i> No reviews found.
                        </div>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <div class="review-user">
                                        <i class="fa fa-user-circle"></i>
                                        <div class="review-user-info">
                                            <h5><?php echo htmlspecialchars($review['user_name'] ?? 'Anonymous User'); ?></h5>
                                            <p><?php echo htmlspecialchars($review['email'] ?? 'No email provided'); ?></p>
                                        </div>
                                    </div>
                                    <div class="review-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa <?php echo $i <= $review['rating'] ? 'fa-star' : 'fa-star-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <div class="review-content">
                                    <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                </div>
                                
                                <div class="review-meta">
                                    <div class="review-date">
                                        <i class="fa fa-calendar"></i>
                                        <span><?php echo date('F j, Y, g:i a', strtotime($review['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="js/bootstrap.js"></script>
</body>
</html> 