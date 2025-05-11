<?php
// Initialize session
require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/Location.php';


$page_title = 'Manage Locations';

// Create Location object
$location = new Location();

// Handle delete location
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Delete location
    if ($location->deleteLocation($id)) {
        $_SESSION['flash_message'] = 'Location deleted successfully!';
        $_SESSION['flash_type'] = 'success';
    } else {
        $_SESSION['flash_message'] = 'Unable to delete location. It may be in use.';
        $_SESSION['flash_type'] = 'danger';
    }
    
    header('Location: /admin/locations.php');
    exit;
}

// Get filter parameter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Get locations based on filter
switch ($filter) {
    case 'available':
        $locations = $location->getAvailableLocations();
        break;
    case 'full':
        $locations = $location->getFullLocations();
        break;
    default:
        // Get all locations with availability info
        $db = new Database();
        $db->query('SELECT l.*, 
                   (l.number_of_stations - COUNT(CASE WHEN cs.status = "active" THEN 1 END)) as available_stations 
                   FROM charging_locations l 
                   LEFT JOIN charging_sessions cs ON l.id = cs.location_id AND cs.status = "active" 
                   GROUP BY l.id 
                   ORDER BY l.description ASC');
        $locations = $db->resultSet();
        break;
}

// Include header
include_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1>Manage Charging Locations</h1>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?php echo APP_URL; ?>/admin/add-location.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Add New Location
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Charging Locations</h5>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group" role="group">
                    <a href="?filter=all" class="btn btn-outline-primary <?php echo ($filter == 'all') ? 'active' : ''; ?>">All Locations</a>
                    <a href="?filter=available" class="btn btn-outline-primary <?php echo ($filter == 'available') ? 'active' : ''; ?>">Available Stations</a>
                    <a href="?filter=full" class="btn btn-outline-primary <?php echo ($filter == 'full') ? 'active' : ''; ?>">Full Locations</a>
                </div>
            </div>
        </div>
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
                    <?php endif; ?>
                    
                    <?php foreach ($locations as $loc) : ?>
                        <tr>
                            <td><?php echo $loc->id; ?></td>
                            <td><?php echo $loc->description; ?></td>
                            <td><?php echo $loc->number_of_stations; ?></td>
                            <td>
                                <?php 
                                    $available = isset($loc->available_stations) ? $loc->available_stations : 0;
                                    if ($available > 0) {
                                        echo '<span class="text-success">' . $available . '</span>';
                                    } else {
                                        echo '<span class="text-danger">0 (Full)</span>';
                                    }
                                ?>
                            </td>
                            <td>$<?php echo number_format($loc->cost_per_hour, 2); ?></td>
                            <td>
                                <a href="<?php echo APP_URL; ?>/admin/edit-location.php?id=<?php echo $loc->id; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="<?php echo APP_URL; ?>/admin/locations.php?delete=<?php echo $loc->id; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this location?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>