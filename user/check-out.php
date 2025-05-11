<?php
if ( ! defined("root") ) {
    define('root', $_SERVER['DOCUMENT_ROOT']);
  }
require_once (root .'/includes/header.php');


// Require login
requireLogin();

// Redirect if admin
if(isAdmin()) {
    header('Location: admin_locations.php');
    exit;
}

// Initialize charging session object
$chargingSession = new ChargingSession();

// Check if session ID is provided
if(!isset($_GET['session_id']) || empty($_GET['session_id'])) {
    $_SESSION['message'] = 'Invalid session ID';
    $_SESSION['message_type'] = 'danger';
    header('Location: /user/my-dashboard.php');
    exit;
}

$sessionId = (int)$_GET['session_id'];

// Process check-out
$result = $chargingSession->checkOut($sessionId);
if($result) {
    $_SESSION['message'] = 'You have successfully checked out. Total cost: $' . $result['total_cost'];
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Failed to check out. Please try again.';
    $_SESSION['message_type'] = 'danger';
}

header('Location: my-dashboard.php');
exit;
?>