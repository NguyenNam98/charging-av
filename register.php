<?php
$page_title = 'Register';
require_once 'includes/header.php';
require_once 'includes/auth.php';


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
$errors = [];

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $user_type = isset($_POST['user_type']) ? trim($_POST['user_type']) : 'User';

    // Validate form data
    if(empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if(empty($email)) {
        $errors[] = 'Email is required';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email is not valid';
    }
    
    if(empty($phone)) {
        $errors[] = 'Phone number is required';
    }
    
    if(empty($password)) {
        $errors[] = 'Password is required';
    } elseif(strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if($password != $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    // Check if email already exists
    $user = new User();
    if($user->findUserByEmail($email)) {
        $errors[] = 'Email is already registered';
    }
    
    // If no errors, register user
    if(empty($errors)) {
        if($user->register([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'user_type' => $user_type
        ])) {
            $_SESSION['message'] = 'Registration successful! You can now login.';
            $_SESSION['message_type'] = 'success';
            header('Location: login.php');
            exit;
        } else {
            $errors[] = 'Something went wrong';
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="m-0">Register</h4>
            </div>
            <div class="card-body">
                <?php if(!empty($errors)) : ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach($errors as $error) : ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">User Type</label>
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