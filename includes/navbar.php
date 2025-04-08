<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $cart_query = "SELECT COUNT(*) as count FROM Cart WHERE user_id = ?";
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_count = $result->fetch_assoc()['count'];
}
?>
<!-- header section strats -->
<header class="header_section">
    <div class="container">
        <nav class="navbar navbar-expand-lg custom_nav-container">
            <a class="navbar-brand" href="index.php">
                <span>Resto</span>
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class=""></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="user.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="menu.php">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="book.php">Book Table</a>
                    </li>

                </ul>
                <div class="user_option">
                    <?php if (isset($_SESSION['user_id'])): ?>
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
                                <a class="dropdown-item" href="reservations.php">
                                    <i class="fa fa-calendar" aria-hidden="true"></i> My Reservations
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
                    <?php else: ?>
                        <a href="login.php" class="user_link">
                            <i class="fa fa-user" aria-hidden="true"></i>
                        </a>
                    <?php endif; ?>
                    <a class="cart_link" href="cart.php">
                        <i class="fa fa-shopping-cart"></i>
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </nav>
    </div>
</header>
<!-- end header section -->
