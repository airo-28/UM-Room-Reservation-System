<?php $PAGE_CLASS = 'bg-auth'; ?>
<?php
require_once __DIR__.'/../lib/auth.php';
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/helpers.php';

start_session();

if (is_logged_in()) {
  $u = user();
  if ($u['role'] === 'admin') redirect('dashboard.php');
  redirect('dashboard_user.php');
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $full_name = trim($_POST['full_name'] ?? '');
  $email     = trim($_POST['email'] ?? '');
  $password  = $_POST['password'] ?? '';
  $confirm   = $_POST['confirm'] ?? '';

  if ($full_name === '' || $email === '' || $password === '' || $confirm === '') {
    $err = 'All fields are required.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $err = 'Invalid email.';
  } elseif ($password !== $confirm) {
    $err = 'Passwords do not match.';
  } else {
    $exists = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    $exists->execute([$email]);
    if ((int)$exists->fetchColumn() > 0) {
      $err = 'Email is already registered.';
    } else {
      $hash = password_hash($password, PASSWORD_BCRYPT);
      $ins = $pdo->prepare('INSERT INTO users(full_name, email, password_hash, role, created_at) VALUES(?, ?, ?, "user", NOW())');
      $ins->execute([$full_name, $email, $hash]);
      $_SESSION['user'] = [
        'id'   => (int)$pdo->lastInsertId(),
        'name' => $full_name,
        'email'=> $email,
        'role' => 'user'
      ];
      redirect('dashboard_user.php');
    }
  }
}
?>
<?php require_once __DIR__.'/../partials/head.php'; ?>
<?php require_once __DIR__.'/../partials/nav.php'; ?>

<div class="d-flex align-items-center justify-content-center vh-100" data-aos="fade-up">
  <div class="container" style="max-width:520px;">
    <?php if($err): ?><div class="alert alert-danger"><?php echo h($err); ?></div><?php endif; ?>
    <div class="card shadow p-4 border-0" style="border-radius:1rem; backdrop-filter: blur(10px); background: rgba(255,255,255,0.92);">
      <h3 class="mb-3 text-center fw-bold" style="color: var(--brand);">
        <i class="bi bi-person-plus me-1"></i> Create an Account
      </h3>
      <form method="post" action="<?php echo h(base_url('register.php')); ?>">
        <?php csrf_field(); ?>
        <div class="mb-3">
          <label class="form-label fw-semibold">Full Name</label>
          <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Email</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Password</label>
          <input type="password" name="password" class="form-control" minlength="6" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Confirm Password</label>
          <input type="password" name="confirm" class="form-control" minlength="6" required>
        </div>
        <button class="btn btn-primary w-100 py-2 fw-semibold">Register</button>
      </form>
      <div class="mt-3 text-center small">
        <span class="text-muted">Already have an account?</span>
        <a href="<?php echo h(base_url('login.php')); ?>" class="text-decoration-none" style="color: var(--accent); font-weight:600;">Sign in</a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__.'/../partials/footer.php'; ?>
