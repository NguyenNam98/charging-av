<?php
$page_title = 'Register';
require_once 'includes/header.php';

// Redirect if already logged in
if(isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Initialize variables
$name = '';
$email = '';
$phone = '';
$password = '';
$confirm_password = '';
$user_type = 'User'; // Default to regular user
// 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character, at least 8 characters long
$passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

$errors = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'password' => '',
    'confirm_password' => '',
    'general' => [],
];

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $user_type = isset($_POST['user_type']) ? trim($_POST['user_type']) : 'User';

    $user = new User();
    // Validate form data
    if(empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email is not valid';
    } 

    if ($user->emailExists($email)) {
        $errors['email'] = 'Email is already registered';
    }
    
    if (empty($phone)) {
    $errors['phone'] = 'Phone number is required';
    } else {
        // Basic international phone number regex (accepts +, digits, space, dashes, parentheses)
        $sanitizedPhone = trim($phone);
        
        if (!preg_match('/^\+?[0-9\s\-\(\)]{7,20}$/', $sanitizedPhone)) {
            $errors['phone'] = 'Invalid phone number format';
        }
    }
    
    if(empty($password) || !preg_match($passwordPattern, $password)) {
        $errors['password'] = 'Password must contain at least 8 characters, including 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character';
    }
    
    if($password != $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    

    if($user->findUserByEmail($email)) {
        $errors['email'] = 'Email is already registered';
    }
    
    // If no errors, register user
    if(!array_filter($errors)) {
        $registrationSuccess = $user->register([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'user_type' => $user_type
        ]);

        if ($registrationSuccess) {
            $_SESSION['message'] = 'Registration successful! You can now login.';
            $_SESSION['message_type'] = 'success';
            header('Location: login.php');
            exit;
        } else {
            $errors['general'][] = 'Something went wrong during registration. Please try again.';
        }
    }
}

// Helper function to display error messages
function displayError($field) {
    global $errors;
    if (!empty($errors[$field])) {
        return '<div class="invalid-feedback d-block">' . $errors[$field] . '</div>';
    }
    return '';
}

// Helper function to mark a field as invalid if it has errors
function getInputClass($field) {
    global $errors;
    return !empty($errors[$field]) ? 'form-control is-invalid' : 'form-control';
}
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="m-0">Register</h4>
            </div>
            <div class="card-body">
            <?php if(!empty($errors['general'])) : ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach($errors['general'] as $error) : ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="<?php echo getInputClass('name'); ?>" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                        <?php echo displayError('name'); ?>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="<?php echo getInputClass('email'); ?>" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        <?php echo displayError('email'); ?>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="tel" class="<?php echo getInputClass('phone'); ?>" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                        <?php echo displayError('phone'); ?>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="<?php echo getInputClass('password'); ?>" id="password" name="password" required>
                        <?php echo displayError('password'); ?>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="<?php echo getInputClass('confirm_password'); ?>" id="confirm_password" name="confirm_password" required>
                        <?php echo displayError('confirm_password'); ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">User Type <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="user_type" id="user_type_user" value="User" <?php echo $user_type == 'User' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="user_type_user">User</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="user_type" id="user_type_admin" value="Administrator" <?php echo $user_type == 'Administrator' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="user_type_admin">Administrator</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                </form>
                
                <div class="mt-3 text-center">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>