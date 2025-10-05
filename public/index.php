<?php $PAGE_CLASS = 'bg-auth'; ?>
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
<div class="container my-5 py-5" data-aos="fade-up">
  <div class="row align-items-center g-5">
    <div class="col-lg-6">
      <div class="card p-4 shadow-sm border-0" style="backdrop-filter: blur(8px); background: rgba(255,255,255,0.92); border-radius: 1rem;">
        <div class="section-bar mb-3">
          <h1 class="fw-bold mb-0" style="color: var(--brand); font-size: 2.4rem;">
            Welcome to Collab<span style="color: var(--accent);">Space</span>
          </h1>
        </div>
        <p class="lead text-muted">
          Reserve collaboration rooms conveniently within the University of Mindanao. Book by the hour from <strong>8:00 AM</strong> to <strong>9:00 PM</strong> — anytime, anywhere.
        </p>
        <p class="text-muted mb-4">
          Whether it’s a group study, project defense, or meeting, <strong>CollabSpace</strong> helps you find and manage available rooms with ease.
        </p>
        <div class="d-flex flex-wrap gap-3 mt-2">
          <a class="btn btn-primary px-4" href="<?php echo h(base_url('login.php')); ?>"><i class="bi bi-box-arrow-in-right me-1"></i>Sign In</a>
          <a class="btn btn-accent px-4" href="<?php echo h(base_url('register.php')); ?>"><i class="bi bi-person-plus me-1"></i>Create Account</a>
          <a class="btn btn-outline-dark px-4" href="<?php echo h(base_url('rooms_gallery.php')); ?>"><i class="bi bi-collection me-1"></i>Browse Rooms</a>
        </div>
      </div>
    </div>

    <div class="col-lg-6" data-aos="fade-left">
      <div class="card shadow-sm border-0 overflow-hidden" style="border-radius:1rem;">
        <img src="<?php echo h(base_url('../assets/img/ui/landing.jpg')); ?>" alt="Study rooms" class="w-100" style="aspect-ratio: 16/9; object-fit: cover;">
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__.'/../partials/footer.php'; ?>
