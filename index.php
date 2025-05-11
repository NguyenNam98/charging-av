<?php
$page_title = 'Home';
require_once 'includes/header.php';

if(isLoggedIn() && isAdmin()) {
    header('Location: /admin/index.php');
    exit;
}

// Initialize location object
$location = new Location();
$availableLocations = $location->getAvailableLocations();

?>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-body">
                <h1 class="card-title text-center mb-4">Welcome to <?php echo SITE_NAME; ?></h1>
                <p class="lead text-center">The easiest way to find and use electric vehicle charging stations.</p>
                
                <div class="row mt-5">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-charging-station fa-4x mb-3 text-primary"></i>
                                <h3>Find Charging Stations</h3>
                                <p>Locate available EV charging stations at convenient locations near you.</p>
                                <a href="/user/search-locations.php" class="btn btn-primary">Search Locations</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-user fa-4x mb-3 text-success"></i>
                                <h3>Manage Your Account</h3>
                                <p>Register or login to track your charging history and check in/out at stations.</p>
                                <?php if(isLoggedIn()) : ?>
                                    <a href="<?php echo isAdmin() ? '/admin/locations.php' : '/user/my-dashboard.php'; ?>" class="btn btn-success">Go to Dashboard</a>
                                <?php else : ?>
                                    <a href="login.php" class="btn btn-success">Login / Register</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(count($availableLocations) > 0) : ?>
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="m-0">Available Charging Locations</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Available Stations</th>
                                <th>Cost Per Hour</th>
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
                                        <?php if(isLoggedIn() && !isAdmin()) : ?>
                                            <a href="/user/check-in.php?location_id=<?php echo $loc['location_id']; ?>" class="btn btn-sm btn-success">Check In</a>
                                        <?php elseif(!isLoggedIn()) : ?>
                                            <a href="/login.php" class="btn btn-sm btn-secondary">Login to Check In</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>