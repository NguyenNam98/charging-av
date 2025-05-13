<?php
if (!defined("root")) {
    define('root', $_SERVER['DOCUMENT_ROOT']);
}
// Initialize session
require_once root . '/config/config.php';
require_once root . '/classes/Database.php';
require_once root . '/classes/User.php';
require_once root . '/classes/ChargingSession.php';
// Include header
include_once root . '/includes/header.php';

// Check if user is logged in and is an administrator
if (!isLoggedIn()) {
    $_SESSION['message'] = 'You must be logged in as an administrator to view that page';
    $_SESSION['message_type'] = 'danger';
    header('Location: /login.php');
    exit;
}

if (!isAdmin()) {
    $_SESSION['message'] = 'You do not have permission to access this page';
    $_SESSION['message_type'] = 'danger';
    header('Location: /index.php');
    exit;
}

// Validate user_id parameter
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    $_SESSION['message'] = 'Invalid user ID';
    $_SESSION['message_type'] = 'danger';
    header('Location: /admin/manage_users.php');
    exit;
}

$user_id = (int)$_GET['user_id'];

// Create User and ChargingSession objects
$user = new User();
$chargingSession = new ChargingSession();

// Get user information
$userData = $user->getUserById($user_id);
if (!$userData) {
    $_SESSION['message'] = 'User not found';
    $_SESSION['message_type'] = 'danger';
    header('Location: /admin/manage_users.php');
    exit;
}

// Get user's charging history
$chargingHistory = $chargingSession->getUserChargingSessions($user_id);

// Get user's current active session if any
$activeSession = $chargingSession->getUserActiveSession($user_id);

$page_title = 'User Detail: ' . htmlspecialchars($userData['name']);
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1>User Detail: <?php echo htmlspecialchars($userData['name']); ?></h1>
    </div>
    <div class="col-md-4 text-end">
        <a href="/admin/manage_users.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to User Management
        </a>
    </div>
</div>

<!-- User Information Card -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-user me-2"></i>User Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>ID:</strong> <?php echo $userData['user_id']; ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($userData['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($userData['phone']); ?></p>
                <p><strong>User Type:</strong> <span class="badge <?php echo $userData['user_type'] === 'Administrator' ? 'bg-danger' : 'bg-success'; ?>"><?php echo htmlspecialchars($userData['user_type']); ?></span></p>
                <p><strong>Registration Date:</strong> <?php echo date('M d, Y', strtotime($userData['created_at'])); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Active Charging Session (if exists) -->
<?php if ($activeSession): ?>
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-plug me-2"></i>Current Active Session</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Location:</strong> <?php echo htmlspecialchars($activeSession['location_description']); ?></p>
                <p><strong>Check-in Time:</strong> <?php echo date('M d, Y H:i', strtotime($activeSession['start_time'])); ?></p>
            </div>
            <div class="col-md-6">
                <?php
                    $start = new DateTime($activeSession['start_time']);
                    $now = new DateTime();
                    $interval = $start->diff($now);
                    $hours = $interval->h + ($interval->i / 60) + ($interval->s / 3600) + ($interval->days * 24);
                    $cost = $hours * $activeSession['cost_per_hour'];
                ?>
                <p><strong>Duration:</strong>
                    <?php
                    if ($interval->d > 0) {
                        echo $interval->format('%d days, %h hrs, %i mins');
                    } else {
                        echo $interval->format('%h hrs, %i mins');
                    }
                    ?>
                </p>
                <p><strong>Current Cost:</strong> $<?php echo number_format($cost, 2); ?></p>
                <p><strong>Rate:</strong> $<?php echo number_format($activeSession['cost_per_hour'], 2); ?>/hour</p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Charging History -->
<div class="card">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Charging History</h5>
    </div>
    <div class="card-body">
        <?php if (empty($chargingHistory)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No charging history found for this user.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered" id="historyTable">
                    <thead class="table-light">
                        <tr>
                            <th>Session ID</th>
                            <th>Location</th>
                            <th>Check-in Time</th>
                            <th>Check-out Time</th>
                            <th>Duration</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chargingHistory as $session): ?>
                            <tr>
                                <td><?php echo $session['session_id']; ?></td>
                                <td><?php echo htmlspecialchars($session['location_description']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($session['start_time'])); ?></td>
                                <td>
                                    <?php 
                                    if ($session['end_time']) {
                                        echo date('M d, Y H:i', strtotime($session['end_time']));
                                    } else {
                                        echo '<span class="badge bg-warning text-dark">Active</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($session['end_time']) {
                                        $start = new DateTime($session['start_time']);
                                        $end = new DateTime($session['end_time']);
                                        $interval = $start->diff($end);
                                        
                                        if ($interval->d > 0) {
                                            echo $interval->format('%d days, %h hrs, %i mins');
                                        } else {
                                            echo $interval->format('%h hrs, %i mins');
                                        }
                                    } else {
                                        // For active sessions, calculate from start to now
                                        $start = new DateTime($session['start_time']);
                                        $now = new DateTime();
                                        $interval = $start->diff($now);
                                        
                                        if ($interval->d > 0) {
                                            echo $interval->format('%d days, %h hrs, %i mins') . ' (ongoing)';
                                        } else {
                                            echo $interval->format('%h hrs, %i mins') . ' (ongoing)';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($session['total_cost']) {
                                        echo '$' . number_format($session['total_cost'], 2);
                                    } else {
                                        // Calculate current cost for active sessions
                                        $hours = $interval->h + ($interval->i / 60) + ($interval->s / 3600) + ($interval->days * 24);
                                        $cost = $hours * $session['cost_per_hour'];
                                        echo '$' . number_format($cost, 2) . ' (ongoing)';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Add DataTables initialization if you have DataTables library -->
            <script>
                $(document).ready(function() {
                    if ($.fn.DataTable) {
                        $('#historyTable').DataTable({
                            "order": [[2, "desc"]], // Sort by start time by default
                            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
                        });
                    }
                });
            </script>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>