<?php
require_once __DIR__.'/../partials/head.php';
require_once __DIR__.'/../partials/nav.php';
require_role(['admin']);
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../lib/helpers.php';

function q1($pdo,$sql,$args=[]){ $st=$pdo->prepare($sql); $st->execute($args); return $st->fetchColumn(); }
function qall($pdo,$sql,$args=[]){ $st=$pdo->prepare($sql); $st->execute($args); return $st->fetchAll(); }

$today        = date('Y-m-d');
$totalRooms   = (int)q1($pdo, "SELECT COUNT(*) FROM rooms");
$totalUsers   = (int)q1($pdo, "SELECT COUNT(*) FROM users");
$todayRes     = (int)q1($pdo, "SELECT COUNT(*) FROM reservations WHERE date=?", [$today]);
$pendingCount = (int)q1($pdo, "SELECT COUNT(*) FROM reservations WHERE status='pending'");

$recent = qall($pdo, "
  SELECT r.date, r.start_time, r.end_time, r.status, u.full_name, rm.name AS room_name
  FROM reservations r
  JOIN users u ON u.id=r.user_id
  JOIN rooms rm ON rm.id=r.room_id
  ORDER BY r.date DESC, r.start_time DESC
  LIMIT 3
");

$pending = qall($pdo, "
  SELECT r.id, r.date, r.start_time, r.end_time, r.purpose, u.full_name, rm.name AS room_name
  FROM reservations r
  JOIN users u ON u.id=r.user_id
  JOIN rooms rm ON rm.id=r.room_id
  WHERE r.status='pending'
  ORDER BY r.created_at ASC
  LIMIT 3
");

/* Latest active announcements (shown to users) */
$announcements = qall($pdo, "
  SELECT id, title, severity, COALESCE(starts_at, created_at) AS ts
  FROM announcements
  WHERE is_active=1
    AND (starts_at IS NULL OR starts_at <= NOW())
    AND (ends_at   IS NULL OR ends_at   >= NOW())
  ORDER BY COALESCE(starts_at, created_at) DESC
  LIMIT 6
");
?>
<style>
  .kpi-card { border:0; border-radius: var(--radius,1rem); box-shadow: var(--shadow,0 6px 24px rgba(0,0,0,.06)); }
  .kpi-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; }
  .kpi-rooms   { background: rgba(13,110,253,.12); }
  .kpi-users   { background: rgba(25,135,84,.12); }
  .kpi-today   { background: rgba(255,193,7,.18); }
  .kpi-pending { background: rgba(220,53,69,.14); }
  .hover-lift { transition: transform .2s ease, box-shadow .2s ease; }
  .hover-lift:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg,0 18px 60px rgba(0,0,0,.16)); }
  .chip { display:inline-block; padding:.15rem .5rem; border-radius:999px; font-size:.75rem; background:#f1f3f5; }
  .status-badge { font-size:.75rem; }
</style>

<div class="container my-3">
  <?php require __DIR__.'/../partials/flash.php'; ?>

  <!-- Top header for consistency -->
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
    <div class="section-bar">
      <span class="fw-semibold"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</span>
    </div>
    <div class="d-none d-md-block text-muted small ms-3">
      Overview of system activity and announcements.
    </div>
  </div>

  <!-- KPI cards -->
  <div class="row g-3">
    <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="0">
      <div class="card kpi-card p-3 hover-lift">
        <div class="d-flex align-items-center gap-3">
          <div class="kpi-icon kpi-rooms"><i class="bi bi-door-open fs-5 text-primary"></i></div>
          <div>
            <div class="text-muted small">Total Rooms</div>
            <div class="h3 mb-0"><?php echo $totalRooms; ?></div>
          </div>
        </div>
        <div class="small text-muted mt-2"><i class="bi bi-sliders me-1"></i>Manage in “Manage Rooms”</div>
      </div>
    </div>
    <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="60">
      <div class="card kpi-card p-3 hover-lift">
        <div class="d-flex align-items-center gap-3">
          <div class="kpi-icon kpi-users"><i class="bi bi-people fs-5 text-success"></i></div>
          <div>
            <div class="text-muted small">Total Users</div>
            <div class="h3 mb-0"><?php echo $totalUsers; ?></div>
          </div>
        </div>
        <div class="small text-muted mt-2"><i class="bi bi-person-badge me-1"></i>Students & staff</div>
      </div>
    </div>
    <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="120">
      <div class="card kpi-card p-3 hover-lift">
        <div class="d-flex align-items-center gap-3">
          <div class="kpi-icon kpi-today"><i class="bi bi-calendar-day fs-5 text-warning"></i></div>
          <div>
            <div class="text-muted small">Today’s Reservations</div>
            <div class="h3 mb-0"><?php echo $todayRes; ?></div>
          </div>
        </div>
        <div class="small text-muted mt-2"><i class="bi bi-clock me-1"></i><?php echo $today; ?></div>
      </div>
    </div>
    <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="180">
      <div class="card kpi-card p-3 hover-lift">
        <div class="d-flex align-items-center gap-3">
          <div class="kpi-icon kpi-pending"><i class="bi bi-hourglass-split fs-5 text-danger"></i></div>
          <div>
            <div class="text-muted small">Pending Approvals</div>
            <div class="h3 mb-0"><?php echo $pendingCount; ?></div>
          </div>
        </div>
        <div class="small mt-2"><a class="text-decoration-none" href="<?php echo h(base_url('calendar.php')); ?>"><i class="bi bi-check2-square me-1"></i>Review now</a></div>
      </div>
    </div>
  </div>

  <!-- Pending Approvals + System Announcements -->
  <div class="row g-3 mt-1">
    <div class="col-lg-7" data-aos="fade-right">
      <div class="card p-3 shadow-sm h-100 hover-lift">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-inbox me-2"></i>Pending Approvals</h5>
          <a class="small" href="<?php echo h(base_url('calendar.php')); ?>">See all →</a>
        </div>
        <?php if($pending): ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead><tr><th>When</th><th>Room</th><th>User</th><th>Purpose</th></tr></thead>
              <tbody>
              <?php foreach($pending as $p): ?>
                <tr>
                  <td class="small"><?php echo h($p['date'].' '.substr($p['start_time'],0,5).'–'.substr($p['end_time'],0,5)); ?></td>
                  <td class="small"><span class="chip"><i class="bi bi-building me-1"></i><?php echo h($p['room_name']); ?></span></td>
                  <td class="small"><?php echo h($p['full_name']); ?></td>
                  <td class="small text-truncate" style="max-width:220px;"><?php echo h($p['purpose']); ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-muted small">No pending reservations.</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-lg-5" data-aos="fade-left">
      <div class="card p-3 shadow-sm h-100 hover-lift">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-megaphone me-2"></i>System Announcements</h5>
          <a class="small" href="<?php echo h(base_url('announcements.php')); ?>"><i class="bi bi-gear me-1"></i>Manage</a>
        </div>
        <?php if($announcements): ?>
          <div class="list-group list-group-flush">
            <?php foreach($announcements as $a): ?>
              <?php
                $sev = strtolower($a['severity']);
                $badge = ($sev==='important') ? 'bg-danger text-white'
                        : (($sev==='notice') ? 'bg-warning text-dark'
                        : (($sev==='update') ? 'bg-success text-white' : 'bg-info text-dark'));
              ?>
              <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <span class="badge <?php echo $badge; ?> me-2"><?php echo h(ucfirst($a['severity'])); ?></span>
                    <strong><?php echo h($a['title']); ?></strong>
                  </div>
                  <small class="text-muted"><?php echo h(date('M j, Y', strtotime($a['ts']))); ?></small>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-muted small">No active announcements.</div>
        <?php endif; ?>
        <hr class="my-3">
        <div class="small"><i class="bi bi-info-circle me-1"></i>Only active announcements within their time window are shown to users.</div>
      </div>
    </div>
  </div>

  <!-- Recent Reservations -->
  <div class="row g-3 mt-1">
    <div class="col-12" data-aos="zoom-in">
      <div class="card p-3 shadow-sm hover-lift">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Reservations</h5>
          <a class="small" href="<?php echo h(base_url('calendar.php')); ?>">Open calendar →</a>
        </div>
        <?php if($recent): ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead><tr><th>When</th><th>Room</th><th>User</th><th>Status</th></tr></thead>
              <tbody>
              <?php foreach($recent as $r): ?>
                <?php $s=strtolower($r['status']); ?>
                <tr>
                  <td class="small"><?php echo h($r['date'].' '.substr($r['start_time'],0,5).'–'.substr($r['end_time'],0,5)); ?></td>
                  <td class="small"><span class="chip"><i class="bi bi-building me-1"></i><?php echo h($r['room_name']); ?></span></td>
                  <td class="small"><?php echo h($r['full_name']); ?></td>
                  <td class="small">
                    <span class="badge rounded-pill status-badge
                      <?php echo $s==='approved'?'bg-success':($s==='pending'?'bg-warning text-dark':'bg-danger'); ?>">
                      <i class="bi <?php echo $s==='approved'?'bi-check2-circle':($s==='pending'?'bi-hourglass-split':'bi-x-circle'); ?> me-1"></i>
                      <?php echo h(ucfirst($r['status'])); ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-muted small">No reservations yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__.'/../partials/footer.php'; ?>
