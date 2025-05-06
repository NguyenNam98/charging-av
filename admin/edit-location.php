<?php
// Initialize session
require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/Location.php';

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Administrator') {
    $_SESSION['flash_message'] = 'You must be logged in as an administrator to view that page';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . APP_URL . '/auth/login.php');
    exit;
}

$page_title = 'Edit Charging Location';

// Create Location object
$location = new Location();

// Set defaults
$location_id = '';
$description = '';
$number_of_stations = '';
$cost_per_hour = '';
$errors = [];

// Check if location ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['flash_message'] = 'Location ID is required';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . APP_URL . '/admin/locations.php');
    exit;
}

$location_id = $_GET['id'];

// Get location details
$location_data = $location->getLocationById($location_id);

if (!$location_data) {
    $_SESSION['flash_message'] = 'Location not found';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . APP_URL . '/admin/locations.php');
    exit;
}

// Pre-fill form with location data
$description = $location_data['description'];
$number_of_stations = $location_data['number_of_stations'];
$cost_per_hour = $location_data['cost_per_hour'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $description = trim($_POST['description']);
    $number_of_stations = trim($_POST['number_of_stations']);
    $cost_per_hour = trim($_POST['cost_per_hour']);
    
    // Description is required
    if (empty($description)) {
        $errors['description'] = 'Description is required';
    }
    
    // Number of stations must be a positive integer
    if (empty($number_of_stations) || !is_numeric($number_of_stations) || $number_of_stations <= 0 || floor($number_of_stations) != $number_of_stations) {
        $errors['number_of_stations'] = 'Number of stations must be a positive integer';
    }
    
    // Cost per hour must be a positive number
    if (empty($cost_per_hour) || !is_numeric($cost_per_hour) || $cost_per_hour <= 0) {
        $errors['cost_per_hour'] = 'Cost per hour must be a positive number';
    }
    
    // If no errors, update the location
    if (empty($errors)) {
        $data = [
            'id' => $location_id,
            'description' => $description,
            'number_of_stations' => $number_of_stations,
            'cost_per_hour' => $cost_per_hour
        ];
        
        if ($location->updateLocation($data)) {
            $_SESSION['flash_message'] = 'Location updated successfully!';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . APP_URL . '/admin/locations.php');
            exit;
        } else {
            $_SESSION['flash_message'] = 'Failed to update location. Please try again.';
            $_SESSION['flash_type'] = 'danger';
        }
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1>Edit Charging Location</h1>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?php echo APP_URL; ?>/admin/locations.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Locations
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Edit Location Details</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $location_id); ?>" method="post">
                    <input type="hidden" name="location_id" value="<?php echo htmlspecialchars($location_id); ?>">
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Location Description</label>
                        <input type="text" class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" 
                               id="description" name="description" value="<?php echo htmlspecialchars($description); ?>" required>
                        <?php if (isset($errors['description'])) : ?>
                            <div class="invalid-feedback">
                                <?php echo $errors['description']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="number_of_stations" class="form-label">Number of Charging Stations</label>
                        <input type="number" class="form-control <?php echo isset($errors['number_of_stations']) ? 'is-invalid' : ''; ?>" 
                               id="number_of_stations" name="number_of_stations" min="1" step="1" 
                               value="<?php echo htmlspecialchars($number_of_stations); ?>" required>
                        <?php if (isset($errors['number_of_stations'])) : ?>
                            <div class="invalid-feedback">
                                <?php echo $errors['number_of_stations']; ?>
                            </div>
                        <?php endif; ?>
                        <div class="form-text">Enter the total number of charging stations available at this location.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cost_per_hour" class="form-label">Cost per Hour ($)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control <?php echo isset($errors['cost_per_hour']) ? 'is-invalid' : ''; ?>" 
                                   id="cost_per_hour" name="cost_per_hour" min="0.01" step="0.01" 
                                   value="<?php echo htmlspecialchars($cost_per_hour); ?>" required>
                            <?php if (isset($errors['cost_per_hour'])) : ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['cost_per_hour']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-text">Enter the cost per hour for charging at this location.</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Location
                        </button>
                        <a href="<?php echo APP_URL; ?>/admin/locations.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php';