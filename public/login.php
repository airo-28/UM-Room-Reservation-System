<?php $PAGE_CLASS = 'bg-auth'; ?>

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
<div class="d-flex align-items-center justify-content-center vh-100" data-aos="fade-up">
  <div class="container" style="max-width:480px;">
    <?php require __DIR__.'/../partials/flash.php'; ?>
    <div class="card shadow p-4 border-0" style="border-radius: 1rem;">
      <h3 class="mb-3 text-center fw-bold" style="color: var(--brand);">
        <i class="bi bi-box-arrow-in-right me-1"></i>Login
      </h3>
      <form method="post" action="<?php echo h(base_url('../actions/auth_login.php')); ?>">
        <?php csrf_field(); ?>
        <div class="mb-3">
          <label class="form-label fw-semibold">Email</label>
          <input required type="email" name="email" class="form-control" autocomplete="username">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Password</label>
          <input required type="password" name="password" class="form-control" autocomplete="current-password">
        </div>
        <button class="btn btn-primary w-100 py-2 fw-semibold">Sign In</button>
      </form>
      <div class="text-center mt-3 small">
        <span class="text-muted">Don’t have an account?</span>
        <a href="<?php echo h(base_url('register.php')); ?>" class="text-decoration-none" style="color: var(--accent); font-weight:600;">Register here</a>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/../partials/footer.php'; ?>
