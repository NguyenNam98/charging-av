<?php
require_once '../config/config.php';
require_once '../classes/Database.php';
require_once '../classes/Location.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Administrator') {
    $_SESSION['flash_message'] = 'You must be logged in as an administrator to view that page';
    $_SESSION['flash_type'] = 'danger';
    header('Location: /auth/login.php');
    exit;
}

$page_title = 'Edit Charging Location';
$location = new Location();
$errors = [];

// Validate ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['flash_message'] = 'Location ID is required';
    $_SESSION['flash_type'] = 'danger';
    header('Location: /admin/locations.php');
    exit;
}

$location_id = $_GET['id'];
$location_data = $location->getLocationById($location_id);

if (!$location_data) {
    $_SESSION['flash_message'] = 'Location not found';
    $_SESSION['flash_type'] = 'danger';
    header('Location: /admin/locations.php');
    exit;
}

// Populate fields
$description = $location_data['description'];
$number_of_stations = $location_data['num_stations'];
$cost_per_hour = $location_data['cost_per_hour'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description']);
    $number_of_stations = trim($_POST['number_of_stations']);
    $cost_per_hour = trim($_POST['cost_per_hour']);

    if (empty($description)) {
        $errors['description'] = 'Description is required';
    }
    if (empty($number_of_stations) || !is_numeric($number_of_stations) || $number_of_stations <= 0 || floor($number_of_stations) != $number_of_stations) {
        $errors['number_of_stations'] = 'Number of stations must be a positive integer';
    }
    if (empty($cost_per_hour) || !is_numeric($cost_per_hour) || $cost_per_hour <= 0) {
        $errors['cost_per_hour'] = 'Cost per hour must be a positive number';
    }

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
            header('Location: /admin/locations.php');
            exit;
        } else {
            $_SESSION['flash_message'] = 'Failed to update location. Please try again.';
            $_SESSION['flash_type'] = 'danger';
        }
    }
}

include_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <!-- <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/locations.php">Charging Locations</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Location</li>
                </ol>
            </nav> -->
            <h1 class="h2 mb-0">Edit Charging Location</h1>
            <p class="text-muted">Update the details for this charging station location.</p>
        </div>
        <div class="col-md-4 text-end d-flex align-items-center justify-content-end">
            <a href="/admin/locations.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Locations
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
            <?php 
                echo $_SESSION['flash_message']; 
                unset($_SESSION['flash_message'], $_SESSION['flash_type']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-edit me-2"></i>Edit Location Details</h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $location_id); ?>" method="post" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Location Description <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" 
                                       id="description" name="description" 
                                       value="<?php echo htmlspecialchars($description); ?>" 
                                       placeholder="e.g. Wollongong" required>
                                <?php if (isset($errors['description'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['description']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="number_of_stations" class="form-label">Number of Charging Stations <span class="text-danger">*</span></label>
                                <input type="number" class="form-control <?php echo isset($errors['number_of_stations']) ? 'is-invalid' : ''; ?>" 
                                       id="number_of_stations" name="number_of_stations" 
                                       min="1" step="1" 
                                       value="<?php echo htmlspecialchars($number_of_stations); ?>" 
                                       placeholder="e.g. 5" required>
                                <?php if (isset($errors['number_of_stations'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['number_of_stations']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="cost_per_hour" class="form-label">Cost per Hour ($) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">$</span>
                                    <input type="number" class="form-control <?php echo isset($errors['cost_per_hour']) ? 'is-invalid' : ''; ?>" 
                                           id="cost_per_hour" name="cost_per_hour" 
                                           min="0.01" step="0.01" 
                                           value="<?php echo htmlspecialchars($cost_per_hour); ?>" 
                                           placeholder="e.g. 5.99" required>
                                    <span class="input-group-text bg-light">/hour</span>
                                    <?php if (isset($errors['cost_per_hour'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['cost_per_hour']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <a href="/admin/locations.php" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 py-2">
                                <i class="fas fa-save me-2"></i>Update Location
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4 bg-light border-0">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted"><i class="fas fa-info-circle me-2"></i>Reminder</h6>
                    <p class="card-text small">Once updated, users will see the new details immediately when browsing locations or checking in.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';
    window.addEventListener('load', function () {
        const forms = document.getElementsByClassName('needs-validation');
        Array.prototype.forEach.call(forms, function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
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
