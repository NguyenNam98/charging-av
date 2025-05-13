<?php
// Initialize & bootstrap
if ( ! defined("root") ) {
  define('root', $_SERVER['DOCUMENT_ROOT']);
}
require_once (root . '/config/config.php');
require_once (root . '/classes/Database.php');
require_once (root . '/classes/User.php');
require_once (root . '/includes/header.php');


requireLogin();
// 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character, at least 8 characters long
$passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
$page_title = 'Change Password';
$user = new User();
$userData = $user->getUserById($_SESSION['user_id']);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'];
    $newPassword    = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // validate current
    if (!preg_match($passwordPattern, $current)) {
        $errors['current_password'] = 'Current password must contain at least 8 characters, including 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character';
    } elseif (! password_verify($current, $userData['password'])) {
        $errors['current_password'] = 'Incorrect current password';
    }

    // validate new password
    if (!preg_match($passwordPattern, $newPassword)) {
        $errors['new_password'] = 'New password must contain at least 8 characters, including 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character';
    }
    // check if new password matches the confirm password
    if ($newPassword !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords must match ';
    }

    if (empty($errors)) {
        $isUpdateSucceed = $user->updatePassword([
            'user_id'       => $_SESSION['user_id'],
            'password' => $newPassword
        ]);
        if ($isUpdateSucceed) {
            $_SESSION['message'] = 'Password changed!';
            $_SESSION['message_type']    = 'success';
            echo '<script>window.location.href = "/profile.php";</script>';

            exit;
        } else {
            $_SESSION['message'] = 'Update failed.';
            $_SESSION['message_type']    = 'danger';
        }
    }
}
?>

<div class="row mb-4">
  <div class="col-8"><h1>Change Password</h1></div>
</div>


<form method="post" class="row g-3">
  <div class="col-md-4">
    <label class="form-label">Current Password</label>
    <input type="password" name="current_password"
           class="form-control <?= isset($errors['current_password'])?'is-invalid':'' ?>" required>
    <div class="invalid-feedback"><?= $errors['current_password']??'' ?></div>
  </div>

  <div class="col-md-4">
    <label class="form-label">New Password</label>
    <input type="password" name="new_password"
           class="form-control <?= isset($errors['new_password'])?'is-invalid':'' ?>" required>
    <div class="invalid-feedback"><?= $errors['new_password']??'' ?></div>
    <div class="form-text">Min 8 characters</div>
  </div>

  <div class="col-md-4">
    <label class="form-label">Confirm New</label>
    <input type="password" name="confirm_password"
           class="form-control <?= isset($errors['confirm_password'])?'is-invalid':'' ?>" required>
    <div class="invalid-feedback"><?= $errors['confirm_password']??'' ?></div>
  </div>

  <div class="col-12">
    <button type="submit" class="btn btn-warning">
      <i class="fas fa-key me-1"></i>Change Password
    </button>
    <a href="profile.php" class="btn btn-secondary">
      <i class="fas fa-arrow-left me-1"></i>Back to Profile
    </a>
  </div>
</form>

<?php include_once (root . '/includes/footer.php'); ?>
