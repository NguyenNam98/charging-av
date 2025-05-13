<?php

if ( ! defined("root") ) {
    define('root', $_SERVER['DOCUMENT_ROOT']);
}
// Admin Dashboard
require_once root .'/config/config.php';
require_once root .'/classes/Database.php';     
require_once root .'/classes/User.php';
require_once root .'/classes/Location.php';
require_once root .'/classes/ChargingSession.php';

date_default_timezone_set(timezoneId: 'Australia/Sydney');

// Check if user is logged in and is an administrator
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'Administrator') {
    $_SESSION['flash_message'] = 'Unauthorized access. Please login as administrator.';
    $_SESSION['flash_type'] = 'danger';
    header('Location:  /auth/login.php');
    exit();
}

$page_title = 'Admin Dashboard';
$users = new User();
$locations = new Location();
$chargingSession = new ChargingSession();
$recent_checkins = $chargingSession->getAllActiveSessions();


// Include header
include_once root .'/includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard</h1>
        <p class="lead">Welcome to the VNN922-EASYEV administration area.</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-users me-2"></i> Total Users</h5>
                <h2 class="display-4"><?php echo $users->getTotalUsers(); ?></h2>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="/admin/users.php">View Details</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-map-marker-alt me-2"></i> Total Locations</h5>
                <h2 class="display-4"><?php echo $locations->getTotalLocation(); ?></h2>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="<?php ?>/admin/locations.php">View Details</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-white mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-plug me-2"></i> Active Charging Sessions</h5>
                <h2 class="display-4"><?php echo $chargingSession->getTotalActiveSessions(); ?></h2>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="<?php ?>/admin/users.php">View Details</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                Recent Active Charging Sessions
            </div>
            <div class="card-body">
                <?php if ($recent_checkins): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>User</th>
                                    <th>Location</th>
                                    <th>Check-in Time</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach($recent_checkins as $checkin): ?>
                                <?php 
                                $check_in_time = new DateTime($checkin['check_in_time'] ?? 'now');
                                $current_time = new DateTime();
                                $interval = $check_in_time->diff($current_time);
                                
                                $duration = $interval->days > 0
                                    ? $interval->format('%d days, %h hours, %i minutes')
                                    : $interval->format('%h hours, %i minutes');
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($checkin['user_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($checkin['location_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo isset($checkin['check_in_time']) ? date('M d, Y h:i A', strtotime($checkin['check_in_time'])) : 'N/A'; ?></td>
                                    <td><?php echo $duration; ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center">No active charging sessions.</p>
                <?php endif; ?>
            </div>
            <div class="card-footer small text-muted">
                <a href="<?php ?>/admin/users.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-list me-1"></i> View All Active Sessions
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-cogs me-1"></i>
                Quick Actions
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="<?php ?>/admin/add-location.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-plus-circle me-2"></i> Add New Charging Location
                    </a>
                    <a href="<?php  ?>/admin/locations.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-map-marker-alt me-2"></i> Manage Charging Locations
                    </a>
                    <a href="<?php ?>/admin/users.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i> Manage Users
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-1"></i>
                System Information
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <td><strong>PHP Version:</strong></td>
                            <td><?php echo phpversion(); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Database:</strong></td>
                            <td>MySQL</td>
                        </tr>
                        <tr>
                            <td><strong>Server Time:</strong></td>
                            <td><?php echo date('Y-m-d H:i:s'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>