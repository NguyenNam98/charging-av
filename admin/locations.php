<?php
// Initialize session
require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/Location.php';

$page_title = 'Manage Locations';

// Create Location object
$location = new Location();

// Get filter status from query parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Get locations based on filter
switch ($filter) {
    case 'available':
        $locations = $location->getAvailableLocations();
        break;
    case 'full':
        // Get all locations and filter for full ones
        $allLocations = $location->getFullLocations();
        $locations = array_filter($allLocations, function($loc) {
            return isset($loc['available_stations']) && intval($loc['available_stations']) <= 0;
        });
        break;
    case 'all':
    default:
        $locations = $location->getFullLocations();
        break;
}

// Handle delete location
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Uncomment to enable deletion
    if ($location->deleteLocation($id)) {
        $_SESSION['flash_message'] = 'Location deleted successfully!';
        $_SESSION['flash_type'] = 'success';
    } else {
        $_SESSION['flash_message'] = 'Unable to delete location. It may be in use.';
        $_SESSION['flash_type'] = 'danger';
    }
    
    // Preserve the filter when redirecting
    header('Location: /admin/locations.php' . ($filter !== 'all' ? '?filter=' . $filter : ''));
    exit;
}

// Include header
include_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1>Manage Charging Locations</h1>
    </div>
    <div class="col-md-6 text-end">
        <div class="d-flex justify-content-end">
            <div class="me-3">
                <div class="btn-group" role="group" aria-label="Filter locations">
                    <a href="/admin/locations.php" class="btn btn-outline-secondary <?php echo $filter === 'all' ? 'active' : ''; ?>">
                        All Locations
                    </a>
                    <a href="/admin/locations.php?filter=available" class="btn btn-outline-success <?php echo $filter === 'available' ? 'active' : ''; ?>">
                        Available
                    </a>
                    <a href="/admin/locations.php?filter=full" class="btn btn-outline-danger <?php echo $filter === 'full' ? 'active' : ''; ?>">
                        Full
                    </a>
                </div>
            </div>
            <a href="/admin/add-location.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Add New Location
            </a>
        </div>
    </div>
</div>

<!-- Display flash message if set -->
<?php if(isset($_SESSION['flash_message'])) : ?>
    <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
        <?php 
        echo $_SESSION['flash_message']; 
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Charging Locations</h5>
        <span class="badge bg-<?php echo $filter === 'available' ? 'success' : ($filter === 'full' ? 'danger' : 'secondary'); ?>">
            <?php 
                echo $filter === 'available' ? 'Available Locations' : 
                    ($filter === 'full' ? 'Full Locations' : 'All Locations'); 
            ?>
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Description</th>
                        <th>Total Stations</th>
                        <th>Available Stations</th>
                        <th>Cost per Hour</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($locations)) : ?>
                        <tr>
                            <td colspan="6" class="text-center">No locations found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($locations as $loc) : ?>
                            <tr>
                                <td><?php echo isset($loc['location_id']) ? htmlspecialchars($loc['location_id']) : ''; ?></td>
                                <td><?php echo isset($loc['description']) ? htmlspecialchars($loc['description']) : ''; ?></td>
                                <td><?php echo isset($loc['num_stations']) ? htmlspecialchars($loc['num_stations']) : ''; ?></td>
                                <td>
                                    <?php 
                                        $available = isset($loc['available_stations']) ? intval($loc['available_stations']) : 0;
                                        if ($available > 0) {
                                            echo '<span class="text-success">' . $available . '</span>';
                                        } else {
                                            echo '<span class="text-danger">0 (Full)</span>';
                                        }
                                    ?>
                                </td>
                                <td>$<?php echo isset($loc['cost_per_hour']) ? number_format((float)$loc['cost_per_hour'], 2) : '0.00'; ?></td>
                                <td>
                                    <a href="/admin/edit-location.php?id=<?php echo isset($loc['location_id']) ? $loc['location_id'] : ''; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="/admin/locations.php?delete=<?php echo isset($loc['location_id']) ? $loc['location_id'] : ''; ?><?php echo $filter !== 'all' ? '&filter=' . $filter : ''; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this location?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>