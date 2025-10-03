<?php
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/auth.php';
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../lib/helpers.php';

start_session();

// Only verify CSRF when POSTing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email !== '' && $password !== '' && login($email, $password)) {
  $u = user();
  if ($u && ($u['role'] ?? 'user') === 'admin') {
    set_flash('ok', 'Welcome back, admin!');
    redirect('dashboard.php');
  } else {
    set_flash('ok', 'Welcome back!');
    redirect('dashboard_user.php');
  }
} else {
  set_flash('err', 'Invalid credentials', 'danger');
  redirect('login.php');
}
