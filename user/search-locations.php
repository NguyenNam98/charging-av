<?php
$page_title = 'Search Locations';
if ( ! defined("root") ) {
    define('root', $_SERVER['DOCUMENT_ROOT']);
}
require_once (root . '/includes/header.php');


// Initialize location object
$location = new Location();

// Get search term
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get locations based on search term
$locations = !empty($searchTerm) ? $location->searchLocations($searchTerm) : $location->getAllLocations();
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="m-0">Search Charging Locations</h4>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="search" placeholder="Search by location ID or description" value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                </form>
                
                <?php if(count($locations) > 0) : ?>
                <div class="table-responsive mt-4">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Location</th>
                                <th>Stations</th>
                                <th>Cost Per Hour</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($locations as $loc) : ?>
                                <?php 
                                    // Get available stations
                                    $available = $location->isLocationAvailable($loc['location_id']);
                                ?>
                                <tr>
                                    <td><?php echo $loc['location_id']; ?></td>
                                    <td><?php echo $loc['description']; ?></td>
                                    <td><?php echo $loc['num_stations']; ?></td>
                                    <td>$<?php echo number_format($loc['cost_per_hour'], 2); ?></td>
                                    <td>
                                        <?php if(isLoggedIn() && !isAdmin() && $available) : ?>
                                            <a href="/user/check-in.php?location_id=<?php echo $loc['location_id']; ?>" class="btn btn-sm btn-success">Check In</a>
                                        <?php elseif(isLoggedIn() && !isAdmin() && !$available) : ?>
                                            <button class="btn btn-sm btn-secondary" disabled>Full</button>
                                        <?php elseif(!isLoggedIn()) : ?>
                                            <a href="/login.php" class="btn btn-sm btn-secondary">Login to Check In</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else : ?>
                <div class="alert alert-info mt-4">
                    No locations found. Please try a different search term.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once (root .'/includes/footer.php'); ?>