<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '15041998');
define('DB_NAME', 'easyev');

// Application settings
define('SITE_NAME', 'VNN922-EASYEV');
define('SITE_URL', 'http://localhost:8000/easyev');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);