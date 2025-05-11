<?php
if ( ! defined("root") ) {
    define('root', $_SERVER['DOCUMENT_ROOT']);
}
require_once (root . '/config/config.php');
require_once (root . '/classes/Database.php');
require_once (root . '/classes/User.php');
require_once (root . '/includes/header.php');
require_once 'includes/auth.php';


requireLogin();  // your helper to guard access

$page_title = 'My Profile';
$user = new User();
$userData = $user->getUserById($_SESSION['user_id']);

$name  = $userData['name'];
$email = $userData['email'];
$phone = $userData['phone'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    // validate...
    if ($name === '') {
        $errors['name'] = 'Name is required';
    }
    if ($email === '') {
        $errors['email'] = 'Email is required';
    } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } elseif ($email !== $userData['email'] && $user->emailExists($email)) {
        $errors['email'] = 'That email is already taken';
    }
    if ($phone === '') {
        $errors['phone'] = 'Phone is required';
    }

    if (empty($errors)) {
        $success = $user->updateUser([
            'id'    => $_SESSION['user_id'],
            'name'  => $name,
            'email' => $email,
            'phone' => $phone
        ]);
        if ($success) {
            $_SESSION['message'] = 'Profile updated!';
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
  <div class="col-8"><h1>My Profile</h1></div>
</div>

<?php if (isset($_SESSION['message'])): ?>
  <div class="alert alert-<?=$_SESSION['type']?>">
    <?= $_SESSION['message'] ?>
  </div>
  <?php unset($_SESSION['message'], $_SESSION['type']); ?>
<?php endif; ?>

<form method="post" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Full Name</label>
    <input name="name" class="form-control <?= isset($errors['name'])?'is-invalid':'' ?>"
           value="<?= htmlspecialchars($name) ?>" required>
    <div class="invalid-feedback"><?= $errors['name']??'' ?></div>
  </div>

  <div class="col-md-6">
    <label class="form-label">Email</label>
    <input type="email" name="email"
           class="form-control <?= isset($errors['email'])?'is-invalid':'' ?>"
           value="<?= htmlspecialchars($email) ?>" required>
    <div class="invalid-feedback"><?= $errors['email']??'' ?></div>
  </div>

  <div class="col-md-6">
    <label class="form-label">Phone</label>
    <input name="phone" class="form-control <?= isset($errors['phone'])?'is-invalid':'' ?>"
           value="<?= htmlspecialchars($phone) ?>" required>
    <div class="invalid-feedback"><?= $errors['phone']??'' ?></div>
  </div>

  <div class="col-md-6">
    <label class="form-label">Account Type</label>
    <input class="form-control bg-light" readonly
           value="<?= htmlspecialchars($_SESSION['user_type']) ?>">
  </div>

  <div class="col-12 d-flex justify-content-end gap-2">
  <button type="submit" class="btn btn-primary">
    <i class="fas fa-save me-1"></i>Save Profile
  </button>
  <a href="change-password.php" class="btn btn-warning">
    <i class="fas fa-key me-1"></i>Change Password
  </a>
</div>
</form>

<?php include_once (root . '/includes/footer.php'); ?>
