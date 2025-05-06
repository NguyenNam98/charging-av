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

$page_title = 'Change Password';
$user = new User();
$userData = $user->getUserById($_SESSION['user_id']);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // validate current
    if ($current === '') {
        $errors['current_password'] = 'Please enter current password';
    } elseif (! password_verify($current, $userData['password'])) {
        $errors['current_password'] = 'Incorrect current password';
    }

    // validate new
    if (strlen($new) < 8) {
        $errors['new_password'] = 'At least 8 characters';
    }
    if ($new !== $confirm) {
        $errors['confirm_password'] = 'Passwords must match';
    }

    if (empty($errors)) {
        $ok = $user->updatePassword([
            'id'       => $_SESSION['user_id'],
            'password' => password_hash($new, PASSWORD_DEFAULT)
        ]);
        if ($ok) {
            $_SESSION['message'] = 'Password changed!';
            $_SESSION['type']    = 'success';
            header('Location: profile.php');
            exit;
        } else {
            $_SESSION['message'] = 'Update failed.';
            $_SESSION['type']    = 'danger';
        }
    }
}
?>

<div class="row mb-4">
  <div class="col-8"><h1>Change Password</h1></div>
  <div class="col-4 text-end">
    <a href="profile.php" class="btn btn-secondary">
      <i class="fas fa-arrow-left me-1"></i>Back to Profile
    </a>
  </div>
</div>

<?php if (isset($_SESSION['message'])): ?>
  <div class="alert alert-<?=$_SESSION['type']?>">
    <?= $_SESSION['message'] ?>
  </div>
  <?php unset($_SESSION['message'], $_SESSION['type']); ?>
<?php endif; ?>

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
  </div>
</form>

<?php include_once (root . '/includes/footer.php'); ?>
