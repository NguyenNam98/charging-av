<?php
if ( ! defined("root") ) {
    define('root', $_SERVER['DOCUMENT_ROOT']);
  }
date_default_timezone_set(timezoneId: 'Australia/Sydney');

$page_title = 'My Dashboard';
require_once (root . '/includes/header.php');


// Require login
requireLogin();

// Redirect if admin
if(isAdmin()) {
    header('Location: admin/index.php');
    exit;
}

// Initialize objects
$location = new Location();
$chargingSession = new ChargingSession();

// Get available locations
$availableLocations = $location->getAvailableLocations();

// Get active session
$activeSession = $chargingSession->getActiveSession($_SESSION['user_id']);

// Get past sessions
$pastSessions = $chargingSession->getUserPastSessions($_SESSION['user_id']);
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="h3 mb-4">Welcome, <?php echo $_SESSION['name']; ?></h1>
    </div>
</div>

<?php if($activeSession) : ?>
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow border-left-success">
            <div class="card-header bg-success text-white">
                <h4 class="m-0">Active Charging Session</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Location:</strong> <?php echo $activeSession['description']; ?></p>
                        <p><strong>Check-in Time:</strong> <?php echo date('M d, Y h:i A', strtotime($activeSession['check_in_time'])); ?></p>
                        <p><strong>Now:</strong><?php echo date('M d, Y h:i A'); ?></p>
                        <p><strong>Cost Per Hour:</strong> $<?php echo number_format($activeSession['cost_per_hour'], 2); ?></p>
                        <?php
                            // Calculate current time spent
                            $checkInTime  = new DateTime($activeSession['check_in_time']);
                            $currentTime  = new DateTime();
                            $timeSpent    = $checkInTime->diff($currentTime);

                            // Compute total minutes and then break into hours + minutes
                            $totalMinutes    = $timeSpent->days * 24 * 60
                                            + $timeSpent->h    * 60
                                            + $timeSpent->i;
                            $displayHours    = floor($totalMinutes / 60);
                            $displayMinutes  = $totalMinutes % 60;

                            // Compute fractional hours for cost
                            $totalHoursFloat = $totalMinutes / 60;
                            $estimatedCost   = $totalHoursFloat * $activeSession['cost_per_hour'];
                        ?>  
                        <p><strong>Time Spent:</strong> 
                            <?php 
                                echo $timeSpent->days > 0 ? $timeSpent->days . ' days, ' : '';
                                echo $timeSpent->h . ' hours, ' . $timeSpent->i . ' minutes'; 
                            ?>
                        </p>
                        <p><strong>Estimated Cost:</strong> $<?php echo number_format($estimatedCost, 2); ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="check-out.php?session_id=<?php echo $activeSession['session_id']; ?>" class="btn btn-lg btn-warning">Check Out</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <?php if(!$activeSession && count($availableLocations) > 0) : ?>
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="m-0">Available Charging Locations</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Available</th>
                                <th>Cost/Hour</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($availableLocations as $loc) : ?>
                                <tr>
                                    <td><?php echo $loc['description']; ?></td>
                                    <td><?php echo $loc['available_stations']; ?> / <?php echo $loc['num_stations']; ?></td>
                                    <td>$<?php echo number_format($loc['cost_per_hour'], 2); ?></td>
                                    <td>
                                        <a href="check-in.php?location_id=<?php echo $loc['location_id']; ?>" class="btn btn-sm btn-success">Check In</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="<?php echo !$activeSession && count($availableLocations) > 0 ? 'col-lg-6' : 'col-lg-12'; ?>">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h4 class="m-0">Past Charging Sessions</h4>
            </div>
            <div class="card-body">
                <?php if(count($pastSessions) > 0) : ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pastSessions as $session) : ?>
                                <tr>
                                    <td><?php echo $session['description']; ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($session['check_in_time'])); ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($session['check_out_time'])); ?></td>
                                    <td>$<?php echo number_format($session['total_cost'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else : ?>
                <div class="alert alert-info">
                    You don't have any past charging sessions.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once (root .'/includes/footer.php'); ?>