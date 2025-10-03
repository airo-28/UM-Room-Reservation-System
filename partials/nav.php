<?php
require_once __DIR__.'/../lib/auth.php';
require_once __DIR__.'/../lib/helpers.php';
start_session();
$u = user();
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container-fluid">
    <!-- Brand -->
    <a class="navbar-brand fw-bold text-primary" href="<?php echo h(base_url('index.php')); ?>">
      <i class="bi bi-building-check me-1"></i> CollabSpace
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <?php if ($u): ?>
          <?php if (($u['role'] ?? 'user') === 'admin'): ?>
            <!-- Admin menu -->
            <li class="nav-item"><a class="nav-link" href="<?php echo h(base_url('dashboard.php')); ?>"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo h(base_url('rooms.php')); ?>"><i class="bi bi-door-open me-1"></i>Manage Rooms</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo h(base_url('resources.php')); ?>"><i class="bi bi-box-seam me-1"></i>Resources</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo h(base_url('users.php')); ?>"><i class="bi bi-people me-1"></i>Users</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo h(base_url('calendar.php')); ?>"><i class="bi bi-calendar3 me-1"></i>Calendar</a></li>
          <?php else: ?>
            <!-- User menu -->
            <li class="nav-item"><a class="nav-link" href="<?php echo h(base_url('dashboard_user.php')); ?>"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo h(base_url('reservations.php')); ?>"><i class="bi bi-calendar-plus me-1"></i>Reserve</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo h(base_url('my_reservations.php')); ?>"><i class="bi bi-list-check me-1"></i>My Reservations</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo h(base_url('rooms_gallery.php')); ?>"><i class="bi bi-collection me-1"></i>Browse Rooms</a></li>
          <?php endif; ?>
          <!-- Common -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle me-1"></i><?php echo h($u['name']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item text-danger" href="<?php echo h(base_url('logout.php')); ?>"><i class="bi bi-box-arrow-right me-1"></i>Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <!-- Guest menu -->
          <li class="nav-item"><a class="nav-link" href="<?php echo h(base_url('login.php')); ?>"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo h(base_url('register.php')); ?>"><i class="bi bi-person-plus me-1"></i>Register</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo h(base_url('rooms_gallery.php')); ?>"><i class="bi bi-collection me-1"></i>Browse Rooms</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
