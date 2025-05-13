    <?php
if ( ! defined("root") ) {
  define('root', $_SERVER['DOCUMENT_ROOT']);
}
require_once (root . '/includes/header.php');

// Require login
requireLogin();

// Redirect if admin
if(isAdmin()) {
    header('Location: admin-locations.php');
    exit;
}

// Initialize objects
$location = new Location();
$chargingSession = new ChargingSession();

// Check if already has active session
$activeSession = $chargingSession->getActiveSession($_SESSION['user_id']);

if($activeSession) {
    echo '<script>alert("You already have an active charging session. Please check out first.");</script>';
    $_SESSION['message'] = 'You already have an active charging session. Please check out first.';
    $_SESSION['message_type'] = 'warning';
    header('Location: /user/my-dashboard.php');
    exit;
}

// Check if location ID is provided
if(!isset($_GET['location_id']) || empty($_GET['location_id'])) {
    $_SESSION['message'] = 'Invalid location ID';
    $_SESSION['message_type'] = 'danger';
    header('Location: /user/my-dashboard.php');
    exit;
}

$locationId = (int)$_GET['location_id'];

// Check if location exists and is available
$locationInfo = $location->getLocationById($locationId);

if(!$locationInfo || !$location->isLocationAvailable($locationId)) {
    $_SESSION['message'] = 'This location is not available for check-in';
    $_SESSION['message_type'] = 'danger';
    header('Location: /user/my-dashboard.php');
    exit;
}

// Process check-in
$sessionId = $chargingSession->checkIn($_SESSION['user_id'], $locationId);
if($sessionId) {
    $_SESSION['message'] = 'You have successfully checked in at ' . $locationInfo['description'];
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Failed to check in. Please try again.';
    $_SESSION['message_type'] = 'danger';
}

echo '<script>window.location.href = "/user/my-dashboard.php";</script>';
exit;
?>