<?php
session_start(); 

$page_title = 'Home';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Administrator';
}


if (isLoggedIn() && isAdmin()) {
    header('Location: /admin/index.php');
    exit;
}

header('Location: /user/index.php');
exit;
?>
