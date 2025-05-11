<?php
// Initialize session
require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/Location.php';

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Administrator') {
    $_SESSION['flash_message'] = 'You must be logged in as an administrator to view that page';
    $_SESSION['flash_type'] = 'danger';
    header('Location:  /auth/login.php');
    exit;
}

$page_title = 'Add Charging Location';

// Create Location object
$location = new Location();

// Set defaults
$description = '';
$num_stations = '';
$cost_per_hour = '';
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $description = trim($_POST['description']);
    $num_stations = trim($_POST['num_stations']);
    $cost_per_hour = trim($_POST['cost_per_hour']);
    
    // Description is required
    if (empty($description)) {
        $errors['description'] = 'Description is required';
    }
    
    // Number of stations must be a positive integer
    if (empty($num_stations) || !is_numeric($num_stations) || $num_stations <= 0 || floor($num_stations) != $num_stations) {
        $errors['num_stations'] = 'Number of stations must be a positive integer';
    }
    
    // Cost per hour must be a positive number
    if (empty($cost_per_hour) || !is_numeric($cost_per_hour) || $cost_per_hour <= 0) {
        $errors['cost_per_hour'] = 'Cost per hour must be a positive number';
    }
    
    // If no errors, add the location
    if (empty($errors)) {
        $data = [
            'description' => $description,
            'num_stations' => $num_stations,
            'cost_per_hour' => $cost_per_hour
        ];
        
        if ($location->addLocation($data)) {
            $_SESSION['flash_message'] = 'Location added successfully!';
            $_SESSION['flash_type'] = 'success';
            header('Location: /admin/locations.php');
            exit;
        } else {
            $_SESSION['flash_message'] = 'Failed to add location. Please try again.';
            $_SESSION['flash_type'] = 'danger';
        }
    }
}

// Include header
include_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <!-- <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/locations.php">Charging Locations</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add New Location</li>
                </ol>
            </nav> -->
            <h1 class="h2 mb-0">Add Charging Location</h1>
            <p class="text-muted">Create a new charging station location with available spots and pricing</p>
        </div>
        <div class="col-md-4 text-end d-flex align-items-center justify-content-end">
            <a href="/admin/locations.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Locations
            </a>
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

    <div class="row">
        <div class="col-lg-8 col-md-10 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-map-marker-alt me-2"></i>Location Details
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Location Description <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" 
                                       id="description" name="description" value="<?php echo htmlspecialchars($description); ?>" 
                                       placeholder="e.g. Wollongong" required>
                                <?php if (isset($errors['description'])) : ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['description']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="num_stations" class="form-label">Number of Charging Stations <span class="text-danger">*</span></label>
                                <input type="number" class="form-control <?php echo isset($errors['num_stations']) ? 'is-invalid' : ''; ?>" 
                                       id="num_stations" name="num_stations" min="1" step="1" 
                                       value="<?php echo htmlspecialchars($num_stations); ?>" 
                                       placeholder="e.g. 5" required>
                                <?php if (isset($errors['num_stations'])) : ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['num_stations']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="cost_per_hour" class="form-label">Cost per Hour <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">$</span>
                                    <input type="number" class="form-control <?php echo isset($errors['cost_per_hour']) ? 'is-invalid' : ''; ?>" 
                                           id="cost_per_hour" name="cost_per_hour" min="0.01" step="0.01" 
                                           value="<?php echo htmlspecialchars($cost_per_hour); ?>" 
                                           placeholder="e.g. 5.99" required>
                                    <span class="input-group-text bg-light">/hour</span>
                                    <?php if (isset($errors['cost_per_hour'])) : ?>
                                        <div class="invalid-feedback">
                                            <?php echo $errors['cost_per_hour']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-between">
                            <a href="/admin/locations.php" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 py-2">
                                <i class="fas fa-plus-circle me-2"></i>Add Location
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4 bg-light border-0">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted"><i class="fas fa-info-circle me-2"></i>Note</h6>
                    <p class="card-text small">After creating a location, you'll be able to view and manage it from the locations list. 
                    Users can check-in to locations with available charging stations.</p>
                </div>
            </div> 
        </div>
    </div>
</div>

<script>
// Form validation script
(function() {
  'use strict';
  window.addEventListener('load', function() {
    var forms = document.getElementsByClassName('needs-validation');
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);
})();
</script>

<?php include_once '../includes/footer.php'; ?>