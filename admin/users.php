<?php
if ( ! defined("root") ) {
    define('root', $_SERVER['DOCUMENT_ROOT']);
}
// Initialize session
require_once root .'/config/config.php';
require_once root .'/classes/Database.php';
require_once root .'/classes/User.php';
require_once root.'/classes/ChargingSession.php';
// Include header
include_once root .'/includes/header.php';

// Check if user is logged in and is an administrator
if (!isLoggedIn()) {
    $_SESSION['flash_message'] = 'You must be logged in as an administrator to view that page';
    $_SESSION['flash_type'] = 'danger';
    header('Location: /login.php');
    exit;
}

if (!isAdmin()) {
    $_SESSION['flash_message'] = 'You do not have permission to access this page';
    $_SESSION['flash_type'] = 'danger';
    header('Location: /index.php');
    exit;
}
$page_title = 'Manage Users';

// Create User object
$user = new User();
$chargingSession = new ChargingSession();

 // Get users based on filters
$users = $user->getAllUsers();
$userCheckedIn = $user->getUsersCheckedIn();
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1>Manage Users</h1>
    </div>
    <div class="col-md-4 text-end">
        <a href="/admin/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Admin Dashboard
        </a>
    </div>
</div>

<!-- All Users Table -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>User List</h5>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No users found matching your criteria.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Type</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo $u['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($u['name']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo htmlspecialchars($u['phone']); ?></td>
                                <td>
                                    <span class="badge <?php echo $u['user_type'] === 'Administrator' ? 'bg-danger' : 'bg-success'; ?>">
                                        <?php echo htmlspecialchars($u['user_type']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                <td>
                                    <a href="/admin/checkins.php?user_id=<?php echo $u['user_id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-history me-1"></i>Check-in History
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Currently Checked-in Users -->
<?php if (!empty($userCheckedIn)): ?>
<div class="card">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-plug me-2"></i>Currently Checked-in Users</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Location</th>
                        <th>Check-in Time</th>
                        <th>Duration</th>
                        <th>Current Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($userCheckedIn as $cu): ?>
                        <tr>
                            <td><?php echo $cu['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($cu['name']); ?></td>
                            <td><?php echo htmlspecialchars($cu['email']); ?></td>
                            <td><?php echo htmlspecialchars($cu['phone']); ?></td>
                            <td><?php echo htmlspecialchars($cu['description']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($cu['check_in_time'])); ?></td>
                            <td>
                                <?php
                                    $start = new DateTime($cu['check_in_time']);
                                    $now = new DateTime();
                                    $interval = $start->diff($now);
                                    
                                    if ($interval->d > 0) {
                                        echo $interval->format('%d days, %h hrs, %i mins');
                                    } else {
                                        echo $interval->format('%h hrs, %i mins');
                                    }
                                ?>
                            </td>
                            <td>
                                <?php
                                    $hours = $interval->h + ($interval->i / 60) + ($interval->s / 3600) + ($interval->days * 24);
                                    $cost = $hours * $cu['cost_per_hour'];
                                    echo '$' . number_format($cost, 2);
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include_once '../includes/footer.php'; ?>