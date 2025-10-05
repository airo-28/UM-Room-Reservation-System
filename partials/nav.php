<?php
require_once __DIR__.'/../lib/auth.php';
require_once __DIR__.'/../lib/helpers.php';
start_session();
$u = user();
?>
<style>
  /* Brand styling scoped to navbar to avoid site-wide changes */
  .brand-wrap { display:flex; align-items:center; gap:.5rem; text-decoration:none; }
  .brand-mark { width:34px; height:34px; object-fit:contain; }
  .brand-text {
    font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
    font-weight: 800;
    letter-spacing:.2px;
    line-height:1;
    display:flex; align-items:center; gap:.15rem;
  }
  .brand-text .collab { color:#C62828; }   /* red */
  .brand-text .space  { color:#FFC107; }   /* yellow */
  .navbar.sticky-top { box-shadow: 0 8px 24px rgba(0,0,0,.10); }
</style>

<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
  <div class="container-fluid">
    <!-- Brand -->
    <a class="navbar-brand brand-wrap" href="<?php echo h(base_url('index.php')); ?>">
      <img class="brand-mark" src="<?php echo h(base_url('../assets/img/nav.png')); ?>" alt="CollabSpace">
      <span class="brand-text">
        <span class="collab">Collab</span><span class="space">Space</span>
      </span>
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
            <li class="nav-item"><a class="nav-link" href="<?php echo h(base_url('users.php')); ?>"><i class="bi bi-people me-1"></i>Users</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo h(base_url('calendar.php')); ?>"><i class="bi bi-calendar3 me-1"></i>Calendar</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo h(base_url('transactions.php')); ?>"><i class="bi bi-journal-text me-1"></i>Transactions</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo h(base_url('announcements.php')); ?>"><i class="bi bi-megaphone me-1"></i>Announcements</a></li>
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
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
