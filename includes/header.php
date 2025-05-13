<?php
if ( ! defined("root") ) {
    define('root', $_SERVER['DOCUMENT_ROOT']);
}
require_once (root . '/config/config.php');
require_once (root . '/classes/Database.php');
require_once (root . '/classes/User.php');
require_once (root . '/classes/Location.php');
require_once (root . '/classes/ChargingSession.php');

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is an administrator
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'Administrator';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['message'] = 'Please log in to access this page';
        $_SESSION['message_type'] = 'warning';
        header('Location: /login.php');
        exit;
    }
}

// Redirect if not admin
function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['message'] = 'You do not have permission to access this page';
        $_SESSION['message_type'] = 'danger';
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            padding-top: 56px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .main-content {
            flex: 1;
        }
        .footer {
            padding: 20px 0;
            background-color: #f8f9fa;
        }
        .card {
            margin-bottom: 20px;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .btn-action {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/index.php"><?php echo SITE_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/user/search-locations.php">Search Locations</a>
                    </li>
                    
                    <?php if(isLoggedIn()) : ?>
                        <?php if(isAdmin()) : ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Admin
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                    <li><a class="dropdown-item" href="/admin/locations.php">Manage Locations</a></li>
                                    <li><a class="dropdown-item" href="/admin/users.php">Manage Users</a></li>
                                </ul>
                            </li>
                        <?php else : ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/user/my-dashboard.php">My Dashboard</a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- <li class="nav-item">
                            <a class="nav-link" href="/profile.php">Profile (<?php echo $_SESSION['name']; ?>)</a>
                        </li>
                        <li class="nav-item">
                            <a href="/logout.php" class="nav-link btn btn-primary text-white">
                                Logout
                            </a>
                        </li> -->
                        <li class="nav-item dropdown">
                            <a 
                                class="nav-link dropdown-toggle p-0" 
                                href="#" 
                                id="userDropdown" 
                                role="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false"
                            >
                            <img 
                            src="https://avatar.iran.liara.run/public" 
                            alt="Avatar" 
                            class="rounded-circle" 
                            width="32" 
                            height="32"
                            />
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                <a class="dropdown-item" href="/profile.php">
                                    Profile (<?php echo htmlspecialchars($_SESSION['name'], ENT_QUOTES); ?>)
                                </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="/logout.php">Logout</a>
                                </li>
                            </ul>
                            </li>

                    <?php else : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content py-4">
        <div class="container">
            <?php if(isset($_SESSION['message'])) : ?>
                <div class="alert alert-<?php echo isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info'; ?> alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['message']; 
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
