<header class="header_section">
    <div class="container">
        <nav class="navbar navbar-expand-lg custom_nav-container">
            <a class="navbar-brand" href="../staff.php">
                <span>Resto Staff</span>
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
                <span class=""></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="staff.php">
                            <i class="fa fa-home"></i> Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="menu-items.php">
                            <i class="fa fa-book"></i> Menu Items
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="booking-details.php">
                            <i class="fa fa-calendar-check-o"></i> Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="preorder-management.php">
                            <i class="fa fa-shopping-cart"></i> Preorders
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="view_deliveries.php">
                            <i class="fa fa-truck"></i> Deliveries
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="view_reviews.php">
                            <i class="fa fa-star"></i> Reviews
                        </a>
                    </li>
                </ul>
                
                <div class="user_option">
                    <div class="dropdown">
                        <a href="#" class="user_link dropdown-toggle" id="staffDropdown" 
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-user"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right staff-dropdown">
                            <div class="staff-header">
                                <i class="fa fa-user-circle"></i>
                                <span><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                            </div>
                            <a class="dropdown-item" href="staff-profile.php">
                                <i class="fa fa-user-circle"></i> Profile
                            </a>
                            
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="fa fa-sign-out"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</header>

<style>
    /* Staff header styles to match staff page */
    .header_section {
        background-color: #222831;
        padding: 15px 0;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    }
    
    .navbar-brand {
        display: flex;
        align-items: center;
    }
    
    .navbar-brand span {
        font-weight: 700;
        font-size: 28px;
        color: #ffbe33;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        text-shadow: 2px 2px 3px rgba(0, 0, 0, 0.4);
        font-family: 'Poppins', sans-serif;
    }
    
    .custom_nav-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .custom_nav-container .navbar-nav {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
    }
    
    .custom_nav-container .navbar-nav .nav-item {
        margin: 0 5px;
    }
    
    .custom_nav-container .navbar-nav .nav-item .nav-link {
        color: #f0f0f0;
        text-align: center;
        padding: 10px 15px;
        border-radius: 4px;
        transition: all 0.3s ease;
        font-weight: 500;
        display: flex;
        align-items: center;
        font-family: 'Open Sans', sans-serif;
        letter-spacing: 0.5px;
    }
    
    .custom_nav-container .navbar-nav .nav-item .nav-link:hover {
        color: #ffbe33;
        background-color: rgba(255, 255, 255, 0.08);
        transform: translateY(-2px);
    }
    
    .custom_nav-container .navbar-nav .nav-item .nav-link i {
        margin-right: 8px;
        font-size: 16px;
        color: #ffbe33;
    }
    
    /* Add a subtle active state for the current page */
    .custom_nav-container .navbar-nav .nav-item .nav-link.active {
        background-color: rgba(255, 190, 51, 0.15);
        color: #ffbe33;
    }
    
    .user_option {
        margin-left: 20px;
    }
    
    .user_link {
        color: #ffffff;
        font-size: 18px;
        padding: 10px 12px;
        border-radius: 50%;
        background-color: #ffbe33;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(255, 190, 51, 0.3);
    }
    
    .user_link:hover {
        background-color: #e69c00;
        color: #ffffff;
        transform: scale(1.05);
    }
    
    .staff-dropdown {
        background-color: #ffffff;
        border: none;
        border-radius: 8px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        min-width: 220px;
        padding: 0;
        margin-top: 10px;
        animation: dropdown-fade 0.2s ease;
    }
    
    @keyframes dropdown-fade {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .staff-header {
        background-color: #f8f9fa;
        padding: 15px 16px;
        border-bottom: 1px solid #eee;
        border-radius: 8px 8px 0 0;
        display: flex;
        align-items: center;
    }
    
    .staff-header i {
        color: #ffbe33;
        margin-right: 10px;
        font-size: 18px;
    }
    
    .staff-header span {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-family: 'Open Sans', sans-serif;
    }
    
    .dropdown-item {
        color: #555;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
        font-weight: 500;
    }
    
    .dropdown-item i {
        margin-right: 10px;
        width: 16px;
        text-align: center;
        color: #666;
    }
    
    .dropdown-item:hover {
        background-color: #f5f5f5;
        color: #ffbe33;
    }
    
    .dropdown-item:hover i {
        color: #ffbe33;
    }
    
    .dropdown-item.text-danger {
        color: #dc3545;
    }
    
    .dropdown-item.text-danger:hover {
        background-color: #fff8f8;
        color: #dc3545;
    }
    
    .dropdown-item.text-danger:hover i {
        color: #dc3545;
    }
    
    .dropdown-divider {
        margin: 0;
        border-top: 1px solid #eee;
    }
    
    /* Responsive adjustments */
    @media (max-width: 992px) {
        .custom_nav-container .navbar-nav {
            margin-top: 15px;
            margin-bottom: 15px;
        }
        
        .custom_nav-container .navbar-nav .nav-item {
            margin: 3px 0;
        }
        
        .user_option {
            margin-left: 0;
            margin-top: 10px;
            display: flex;
            justify-content: center;
        }
    }
</style>

<!-- Add Google Fonts link in the head section of your document -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
