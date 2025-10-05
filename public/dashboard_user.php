<?php
require_once __DIR__.'/../partials/head.php';
require_once __DIR__.'/../partials/nav.php';
require_login();
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../lib/helpers.php';

$u = user(); $uid = (int)$u['id'];

function qall($pdo,$sql,$args=[]){ $st=$pdo->prepare($sql); $st->execute($args); return $st->fetchAll(); }
function q1($pdo,$sql,$args=[]){ $st=$pdo->prepare($sql); $st->execute($args); return $st->fetchColumn(); }

$today = date('Y-m-d');
$upcomingCount = (int)q1($pdo, "SELECT COUNT(*) FROM reservations WHERE user_id=? AND date>=? AND status IN ('pending','approved')", [$uid,$today]);
$approvedThisMonth = (int)q1($pdo, "SELECT COUNT(*) FROM reservations WHERE user_id=? AND status='approved' AND DATE_FORMAT(date, '%Y-%m')=DATE_FORMAT(CURDATE(),'%Y-%m')", [$uid]);
$pendingCount = (int)q1($pdo, "SELECT COUNT(*) FROM reservations WHERE user_id=? AND status='pending'", [$uid]);

$upcoming = qall($pdo, "
  SELECT r.id, r.date, r.start_time, r.end_time, r.status,
         rm.name AS room_name, rm.location, rm.capacity
  FROM reservations r
  JOIN rooms rm ON rm.id=r.room_id
  WHERE r.user_id=? AND r.date>=CURDATE() AND r.status IN ('pending','approved')
  ORDER BY r.date ASC, r.start_time ASC
  LIMIT 3
", [$uid]);

$history = qall($pdo, "
  SELECT r.id, r.date, r.start_time, r.end_time, r.status, r.purpose, rm.name AS room_name, rm.location
  FROM reservations r
  JOIN rooms rm ON rm.id=r.room_id
  WHERE r.user_id=? AND (r.date < CURDATE() OR r.status IN ('rejected','canceled'))
  ORDER BY r.date DESC, r.start_time DESC
  LIMIT 3
", [$uid]);

$ann = qall($pdo, "
  SELECT title, body, severity, COALESCE(starts_at, created_at) AS s
  FROM announcements
  WHERE is_active=1
    AND (starts_at IS NULL OR starts_at <= NOW())
    AND (ends_at IS NULL OR ends_at >= NOW())
  ORDER BY s DESC
  LIMIT 5
");
?>
<style>
  .kpi-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center}
  .kpi-upcoming{background:rgba(198,40,40,.12)}
  .kpi-approved{background:rgba(25,135,84,.12)} /* green background for Approved This Month icon */
  .kpi-pending{background:rgba(255,193,7,.28)}
</style>

<div class="container my-3">
  <?php require __DIR__.'/../partials/flash.php'; ?>

  <!-- Welcome header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
    <div class="section-bar">
      <span class="fw-semibold"><i class="bi bi-person-badge me-2"></i>Welcome back, <?php echo h($u['name']); ?>!</span>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-6 col-md-4" data-aos="fade-up" data-aos-delay="0">
      <div class="card kpi-card p-3 hover-lift">
        <div class="d-flex align-items-center gap-3">
          <div class="kpi-icon kpi-upcoming"><i class="bi bi-calendar-event fs-5 text-danger"></i></div>
          <div>
            <div class="text-muted small">Upcoming Bookings</div>
            <div class="h3 mb-0"><?php echo $upcomingCount; ?></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4" data-aos="fade-up" data-aos-delay="60">
      <div class="card kpi-card p-3 hover-lift">
        <div class="d-flex align-items-center gap-3">
          <div class="kpi-icon kpi-approved"><i class="bi bi-patch-check fs-5 text-success"></i></div>
          <div>
            <div class="text-muted small">Approved This Month</div>
            <div class="h3 mb-0"><?php echo $approvedThisMonth; ?></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4" data-aos="fade-up" data-aos-delay="120">
      <div class="card kpi-card p-3 hover-lift">
        <div class="d-flex align-items-center gap-3">
          <div class="kpi-icon kpi-pending"><i class="bi bi-hourglass-split fs-5 text-warning"></i></div>
          <div>
            <div class="text-muted small">Pending Requests</div>
            <div class="h3 mb-0"><?php echo $pendingCount; ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-1">
    <div class="col-lg-7" data-aos="fade-right">
      <div class="card p-3 shadow-sm h-100 hover-lift">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-calendar-week me-2"></i>Upcoming Reservations</h5>
          <a class="small" href="<?php echo h(base_url('my_reservations.php')); ?>">See all →</a>
        </div>
        <?php if($upcoming): ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead><tr><th>When</th><th>Room</th><th>Status</th></tr></thead>
              <tbody>
              <?php foreach($upcoming as $r): ?>
                <?php $s=strtolower($r['status']); $badge = $s==='approved'?'bg-success':($s==='pending'?'bg-warning text-dark':'bg-secondary'); ?>
                <tr>
                  <td class="small"><?php echo h($r['date'].' '.substr($r['start_time'],0,5).'–'.substr($r['end_time'],0,5)); ?></td>
                  <td class="small text-muted">
                    <i class="bi bi-building me-1"></i><?php echo h($r['room_name']); ?>
                  </td>
                  <td class="small"><span class="badge rounded-pill <?php echo $badge; ?>"><?php echo h(ucfirst($r['status'])); ?></span></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-muted small">You have no upcoming reservations.</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-lg-5" data-aos="fade-left">
      <div class="card p-3 shadow-sm h-100 hover-lift">
        <h5 class="mb-2"><i class="bi bi-bell me-2"></i>Announcements</h5>
        <?php if($ann): ?>
          <ul class="list-unstyled mb-0">
            <?php foreach($ann as $a): ?>
              <?php $cls = [
                'update' => 'badge-update',
                'info' => 'badge-info',
                'notice' => 'badge-notice',
                'important' => 'badge-important'
              ][$a['severity']] ?? 'bg-secondary'; ?>
              <li class="mb-2 p-2 rounded hover-lift" style="background:#fff8e1;">
                <span class="badge <?php echo $cls; ?> me-2"><?php echo h(ucfirst($a['severity'])); ?></span>
                <strong><?php echo h($a['title']); ?></strong>
                <div class="small text-muted"><?php echo h($a['body']); ?></div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="text-muted small">No announcements right now.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-1">
    <div class="col-12" data-aos="zoom-in">
      <div class="card p-3 shadow-sm hover-lift">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent History</h5>
          <a class="small" href="<?php echo h(base_url('my_reservations.php')); ?>">See all →</a>
        </div>
        <?php if($history): ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead><tr><th>When</th><th>Room</th><th>Status</th></tr></thead>
              <tbody>
              <?php foreach($history as $r): ?>
                <?php
                  $s=strtolower($r['status']);
                  $badge = $s==='approved'?'bg-success':($s==='pending'?'bg-warning text-dark':($s==='canceled'?'bg-secondary':'bg-danger'));
                ?>
                <tr>
                  <td class="small"><?php echo h($r['date'].' '.substr($r['start_time'],0,5).'–'.substr($r['end_time'],0,5)); ?></td>
                  <td class="small text-muted">
                    <i class="bi bi-building me-1"></i><?php echo h($r['room_name']); ?>
                  </td>
                  <td class="small"><span class="badge rounded-pill <?php echo $badge; ?>"><?php echo h(ucfirst($r['status'])); ?></span></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-muted small">No past reservations yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__.'/../partials/footer.php'; ?>
