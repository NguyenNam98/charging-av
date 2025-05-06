<?php
// Initialize session
require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/Location.php';
require_once '../classes/ChargingSession.php';

// Check if user is logged in and is a regular user
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'User') {
    $_SESSION['flash_message'] = 'You must be logged in as a user to check-in';
    $_SESSION['flash_type'] = 'danger';
    header('Location: ' . APP_URL . '/auth/login.php');
    exit;
}

$page_title = 'Check-in for Charging';

// Create objects
$location = new Location();
$chargingSession = new ChargingSession();

// Get available locations (those with at least one free station)
$available_locations = $location->getAvailableLocations();
$selected_location_id = '';
$selected_location = null;
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate location selection
    $selected_location_id = isset($_POST['location_id']) ? trim($_POST['location_id']) : '';
    
    if (empty($selected_location_id)) {
        $errors['location_id'] = 'Please select a charging location';
    } else {
        // Get location details
        $selected_location = $location->getLocationById($selected_location_id);
        
        if (!$selected_location) {
            $errors['location_id'] = 'Invalid location selected';
        } else {
            // Check if location has available stations
            $available_stations = $location->getAvailableStations($selected_location_id);
            
            if ($available_stations <= 0) {
                $errors['location_id'] = 'This location has no available charging stations';
            }
        }
    }
    
    // If no errors, create a charging session
    if (empty($errors)) {
        $start_time = date('Y-m-d H:i:s');
        $user_id = $_SESSION['user_id'];
        
        $data = [
            'user_id' => $user_id,
            'location_id' => $selected_location_id,
            'start_time' => $start_time,
            'status' => 'active'
        ];
        
        if ($chargingSession->startChargingSession($data)) {
            $_SESSION['flash_message'] = 'Check-in successful! Your charging session has started.';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . APP_URL . '/user/current.php');
            exit;
        } else {
            $_SESSION['flash_message'] = 'Failed to check-in. Please try again.';
            $_SESSION['flash_type'] = 'danger';
        }
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1>Check-in for Charging</h1>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?php echo APP_URL; ?>/user/my-dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Select Charging Location</h5>
            </div>
            <div class="card-body">
                <?php if (empty($available_locations)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>No charging locations with available stations at the moment.
                    </div>
                <?php else: ?>
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                        <div class="mb-3">
                            <label for="location_id" class="form-label">Available Charging Locations</label>
                            <select class="form-select <?php echo isset($errors['location_id']) ? 'is-invalid' : ''; ?>" 
                                   id="location_id" name="location_id" required>
                                <option value="">-- Select a Location --</option>
                                <?php foreach ($available_locations as $loc): ?>
                                    <option value="<?php echo $loc['id']; ?>" 
                                            <?php echo ($selected_location_id == $loc['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($loc['description']); ?> 
                                        (<?php echo $loc['available_stations']; ?> stations available) - 
                                        $<?php echo number_format($loc['cost_per_hour'], 2); ?>/hour
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['location_id'])) : ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['location_id']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle me-3 fs-4"></i>
                                <div>
                                    <p class="mb-1">By checking in, you are starting a charging session that will be billed at the location's hourly rate.</p>
                                    <p class="mb-0">Don't forget to check out when your charging is complete.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plug me-2"></i>Check-in Now
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>