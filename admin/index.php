<?php
// Admin Dashboard
require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';
require_once '../classes/Location.php';
require_once '../classes/Charging.php';

// Check if user is logged in and is an administrator
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'Administrator') {
    $_SESSION['flash_message'] = 'Unauthorized access. Please login as administrator.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . APP_URL . '/auth/login.php');
    exit();
}

$page_title = 'Admin Dashboard';


// Include header
include_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1><i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard</h1>
        <p class="lead">Welcome to the EasyEV-Charging administration area.</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-users me-2"></i> Total Users</h5>
                <h2 class="display-4"><?php echo $total_users; ?></h2>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="<?php echo APP_URL; ?>/admin/users.php">View Details</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-map-marker-alt me-2"></i> Total Locations</h5>
                <h2 class="display-4"><?php echo $total_locations; ?></h2>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="<?php echo APP_URL; ?>/admin/locations.php">View Details</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-white mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-plug me-2"></i> Active Charging Sessions</h5>
                <h2 class="display-4"><?php echo $active_sessions; ?></h2>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="<?php echo APP_URL; ?>/admin/checkins.php">View Details</a>
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
                                    // Calculate duration
                                    $check_in_time = new DateTime($checkin->check_in_time);
                                    $current_time = new DateTime();
                                    $interval = $check_in_time->diff($current_time);
                                    
                                    if ($interval->days > 0) {
                                        $duration = $interval->format('%d days, %h hours, %i minutes');
                                    } else {
                                        $duration = $interval->format('%h hours, %i minutes');
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo $checkin->user_name; ?></td>
                                        <td><?php echo $checkin->location_name; ?></td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($checkin->check_in_time)); ?></td>
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
                <a href="<?php echo APP_URL; ?>/admin/checkins.php" class="btn btn-primary btn-sm">
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
                    <a href="<?php echo APP_URL; ?>/admin/add-location.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-plus-circle me-2"></i> Add New Charging Location
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/locations.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-map-marker-alt me-2"></i> Manage Charging Locations
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/users.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i> Manage Users
                    </a>
                    <a href="<?php echo APP_URL; ?>/search.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-search me-2"></i> Search
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
                            <td><strong>EasyEV-Charging Version:</strong></td>
                            <td>1.0.0</td>
                        </tr>
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