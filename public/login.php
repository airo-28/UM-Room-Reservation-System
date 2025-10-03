<?php
require_once __DIR__.'/../partials/head.php';
require_once __DIR__.'/../partials/nav.php';
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../lib/auth.php';
require_once __DIR__.'/../lib/helpers.php';

start_session();

// If already logged in, redirect by role
if (is_logged_in()) {
  $u = user();
  if (($u['role'] ?? 'user') === 'admin') {
    redirect('dashboard.php');
  } else {
    redirect('dashboard_user.php');
  }
  exit;
}
?>
<div class="container" style="max-width:480px;">
  <?php require __DIR__.'/../partials/flash.php'; ?>
  <div class="card shadow-sm p-4" data-aos="fade-up">
    <h3 class="mb-3"><i class="bi bi-box-arrow-in-right me-1"></i>Login</h3>
    <form method="post" action="<?php echo h(base_url('../actions/auth_login.php')); ?>">
      <?php csrf_field(); ?>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input required type="email" name="email" class="form-control" autocomplete="username">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input required type="password" name="password" class="form-control" autocomplete="current-password">
      </div>
      <button class="btn btn-primary w-100">Sign in</button>
    </form>
  </div>
</div>
<?php require_once __DIR__.'/../partials/footer.php'; ?>
