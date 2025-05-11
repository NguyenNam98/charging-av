<?php
// logout.php

// 1) Bootstrap config & session (no includes that echo HTML)
if (! defined('ROOT')) {
    define('ROOT', realpath(__DIR__) . DIRECTORY_SEPARATOR);
}
require_once ROOT . 'config/config.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2) Clear session data
$_SESSION = [];

// 3) Clear session cookie (if any)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params['path'], 
        $params['domain'],
        $params['secure'], 
        $params['httponly']
    );
}

// 4) Destroy the session
session_destroy();

// 5) Redirect to login with a flash message
session_start();
$_SESSION['message']      = 'You have been logged out';
$_SESSION['message_type'] = 'info';

header('Location: login.php');
exit;
