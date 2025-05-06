<?php
// Initialize session
require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';
require_once '../classes/ChargingSession.php';

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Administrator') {
    $_SESSION['flash_message'] = 'You must be logged in as an administrator to view that page';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . APP_URL . '/auth/login.php');
    exit;
}

$page_title = 'Manage Users';

// Create User object
$user = new User();
$chargingSession = new ChargingSession();

// Get filter parameters
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Get users based on filters
$users = $user->getUsers($filter_type, $search_term, $filter_status);

// Get checked-in users
$checked_in_users = [];
if ($filter_status === 'checked-in' || empty($filter_status)) {
    $checked_in_users = $user->getCheckedInUsers();
}

// Include header
include_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1>Manage Users</h1>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?php echo APP_URL; ?>/admin/index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Admin Dashboard
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Users</h5>
    </div>
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo htmlspecialchars($search_term); ?>" 
                       placeholder="Name, Email, Phone">
            </div>
            <div class="col-md-3">
                <label for="type" class="form-label">User Type</label>
                <select class="form-select" id="type" name="type">
                    <option value="" <?php echo $filter_type === '' ? 'selected' : ''; ?>>All Types</option>
                    <option value="Administrator" <?php echo $filter_type === 'Administrator' ? 'selected' : ''; ?>>Administrator</option>
                    <option value="User" <?php echo $filter_type === 'User' ? 'selected' : ''; ?>>User</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="" <?php echo $filter_status === '' ? 'selected' : ''; ?>>All Users</option>
                    <option value="checked-in" <?php echo $filter_status === 'checked-in' ? 'selected' : ''; ?>>Currently Checked-in</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Filter
                </button>
            </div>
        </form>
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
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
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
                                    <?php 
                                        $is_checked_in = false;
                                        foreach ($checked_in_users as $checked_in) {
                                            if ($checked_in['id'] === $u['id']) {
                                                $is_checked_in = true;
                                                break;
                                            }
                                        }
                                        
                                        if ($is_checked_in): 
                                    ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-plug me-1"></i>Currently Charging
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not Checked-in</span>
                                    <?php endif; ?>
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
<?php if (!empty($checked_in_users) && empty($filter_status)): ?>
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
                    <?php foreach ($checked_in_users as $cu): ?>
                        <tr>
                            <td><?php echo $cu['id']; ?></td>
                            <td><?php echo htmlspecialchars($cu['name']); ?></td>
                            <td><?php echo htmlspecialchars($cu['email']); ?></td>
                            <td><?php echo htmlspecialchars($cu['phone']); ?></td>
                            <td><?php echo htmlspecialchars($cu['location_description']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($cu['start_time'])); ?></td>
                            <td>
                                <?php
                                    $start = new DateTime($cu['start_time']);
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