<?php
require_once __DIR__.'/../lib/auth.php';
require_once __DIR__.'/../lib/helpers.php';
start_session();

if (is_logged_in()) {
  $u = user();
  if ($u['role'] === 'admin') {
    redirect('dashboard.php');
  } else {
    redirect('dashboard_user.php');
  }
  exit;
}

require_once __DIR__.'/../partials/head.php';
require_once __DIR__.'/../partials/nav.php';
?>
<div class="container my-5">
  <div class="row align-items-center g-4">
    <div class="col-lg-6">
      <h1 class="mb-3">Welcome to UM Room Reservations</h1>
      <p class="lead text-muted">Browse rooms, view details, and book hourly slots from 8:00 AM to 9:00 PM.</p>
      <div class="d-flex gap-2 mt-3">
        <a class="btn btn-primary" href="<?php echo h(base_url('login.php')); ?>">Sign in</a>
        <a class="btn btn-outline-secondary" href="<?php echo h(base_url('register.php')); ?>">Create account</a>
        <a class="btn btn-outline-dark" href="<?php echo h(base_url('rooms_gallery.php')); ?>">Browse Rooms</a>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <img src="<?php echo h(base_url('../assets/img/ui/landing.jpg')); ?>" alt="Study rooms" class="w-100" style="aspect-ratio: 16/9; object-fit: cover;">
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/../partials/footer.php'; ?>
