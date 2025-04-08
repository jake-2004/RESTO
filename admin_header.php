<?php
if (!isset($_SESSION)) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<header class="header_section">
    <div class="container">
        <nav class="navbar navbar-expand-lg custom_nav-container">
            <a class="navbar-brand" href="admin.php">
                <span>Resto Admin</span>
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
                <span class=""></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php">
                            <i class="fa fa-tachometer"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="adminlist.php">
                            <i class="fa fa-users"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">
                            <i class="fa fa-cog"></i> Settings
                        </a>
                    </li>
                </ul>
                
                <div class="user_option">
                    <div class="dropdown">
                        <a href="#" class="user_link dropdown-toggle" id="adminDropdown" 
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-user-secret"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right admin-dropdown">
                            <div class="admin-header">
                                <i class="fa fa-user-secret"></i>
                                <span><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                            </div>
                            <a class="dropdown-item" href="change_password.php">
                                <i class="fa fa-cog"></i> Settings
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
    .header_section {
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(8px);
        position: relative;
        z-index: 1;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .navbar-brand {
        color: #ffbe33;
        font-weight: bold;
        font-size: 24px;
        margin-right: 30px;
    }

    .navbar-brand:hover {
        color: #ffbe33;
    }

    .nav-link {
        color: white !important;
        padding: 8px 15px !important;
        margin: 0 5px;
    }

    .nav-link:hover {
        color: #ffbe33 !important;
    }

    .nav-item.active .nav-link {
        color: #ffbe33 !important;
    }

    .user_option .user_link {
        color: #fff;
        font-size: 1.2em;
        padding: 8px;
        border-radius: 50%;
        background: rgba(33, 150, 243, 0.1);
        transition: all 0.3s ease;
        display: inline-block;
    }

    .user_option .user_link:hover {
        background: rgba(33, 150, 243, 0.2);
        transform: translateY(-2px);
    }

    .admin-dropdown {
        background: rgba(25, 28, 36, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        margin-top: 10px;
        min-width: 220px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        padding: 0;
        overflow: hidden;
    }

    .admin-header {
        padding: 16px;
        background: rgba(33, 150, 243, 0.1);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .admin-header i {
        color: var(--admin-primary);
        font-size: 1.2em;
    }

    .admin-header span {
        color: #fff;
        font-size: 0.9em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .admin-dropdown .dropdown-item {
        color: #fff;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.2s ease;
    }

    .admin-dropdown .dropdown-item i {
        width: 20px;
        text-align: center;
        font-size: 1.1em;
    }

    .admin-dropdown .dropdown-item:hover {
        background: rgba(33, 150, 243, 0.1);
        color: var(--admin-primary);
    }

    .admin-dropdown .dropdown-divider {
        border-color: rgba(255, 255, 255, 0.1);
        margin: 0;
    }

    .admin-dropdown .text-danger:hover {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
</style> 