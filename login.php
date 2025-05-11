<?php
$page_title = 'Login';
require_once 'includes/header.php';
require_once 'includes/auth.php';


// Redirect if already logged in
if(isLoggedIn()) {
    header('Location: index.php');
    exit;
}


// Initialize variables
$email = '';
$error = '';

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Validate form data
    if(empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        // Attempt to login
        $user = new User();
        $loggedInUser = $user->login($email, $password);
        
        if($loggedInUser) {
            // Create session variables
            $_SESSION['user_id'] = $loggedInUser['user_id'];
            $_SESSION['name'] = $loggedInUser['name'];
            $_SESSION['email'] = $loggedInUser['email'];
            $_SESSION['user_type'] = $loggedInUser['user_type'];
            
            $_SESSION['message'] = 'You are now logged in';
            $_SESSION['message_type'] = 'success';
            
            if($loggedInUser['user_type'] == 'Administrator') {
                header('Location: /admin/index.php');
            } else {
                header('Location: /user/my-dashboard.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="m-0">Login</h4>
            </div>
            <div class="card-body">
                <?php if($error) : ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
                
                <div class="mt-3 text-center">
                    <p>Don't have an account? <a href="register.php">Register</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>