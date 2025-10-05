<?php
require_once __DIR__.'/../partials/head.php';
require_once __DIR__.'/../partials/nav.php';
require_login();
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../lib/helpers.php';

$u = user();
$uid = (int)$u['id'];

function qall($pdo,$sql,$args=[]){ $st=$pdo->prepare($sql); $st->execute($args); return $st->fetchAll(); }
function q1($pdo,$sql,$args=[]){ $st=$pdo->prepare($sql); $st->execute($args); return $st->fetchColumn(); }

$today = date('Y-m-d');
$upcomingCount = (int)q1($pdo, "SELECT COUNT(*) FROM reservations WHERE user_id=? AND date>=? AND status IN ('pending','approved')", [$uid,$today]);
$approvedThisMonth = (int)q1($pdo, "SELECT COUNT(*) FROM reservations WHERE user_id=? AND status='approved' AND DATE_FORMAT(date, '%Y-%m')=DATE_FORMAT(CURDATE(),'%Y-%m')", [$uid]);
$pendingCount = (int)q1($pdo, "SELECT COUNT(*) FROM reservations WHERE user_id=? AND status='pending'", [$uid]);

$upcoming = qall($pdo, "
  SELECT r.id, r.date, r.start_time, r.end_time, r.status, r.purpose,
         rm.name AS room_name, rm.location
  FROM reservations r
  JOIN rooms rm ON rm.id=r.room_id
  WHERE r.user_id=? AND r.date>=CURDATE() AND r.status IN ('pending','approved')
  ORDER BY r.date ASC, r.start_time ASC
  LIMIT 7
", [$uid]);

$history = qall($pdo, "
  SELECT r.id, r.date, r.start_time, r.end_time, r.status, r.purpose, rm.name AS room_name
  FROM reservations r
  JOIN rooms rm ON rm.id=r.room_id
  WHERE r.user_id=? AND (r.date < CURDATE() OR r.status IN ('rejected','canceled'))
  ORDER BY r.date DESC, r.start_time DESC
  LIMIT 10
", [$uid]);

$announcements = qall($pdo,"
  SELECT id,title,body,severity,starts_at,ends_at,created_at
  FROM announcements
  WHERE is_active=1
    AND (starts_at IS NULL OR starts_at<=NOW())
    AND (ends_at IS NULL OR ends_at>=NOW())
  ORDER BY COALESCE(starts_at,created_at) DESC
  LIMIT 6
");
?>
<style>
  .kpi-card{border:0;border-radius:1rem;box-shadow:0 6px 24px rgba(0,0,0,.06)}
  .kpi-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center}
  .kpi-upcoming{background:rgba(13,110,253,.12)}
  .kpi-approved{background:rgba(25,135,84,.12)}
  .kpi-pending{background:rgba(255,193,7,.18)}
  .hover-lift{transition:transform .2s ease,box-shadow .2s ease}
  .hover-lift:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(0,0,0,.10)}
  .chip{display:inline-block;padding:.15rem .5rem;border-radius:999px;font-size:.75rem;background:#f1f3f5}
</style>

<div class="container my-3">
  <?php require __DIR__.'/../partials/flash.php'; ?>
  <h4 class="mb-3">Welcome back, <?php echo h($u['name']); ?>!</h4>

  <div class="row g-3">
    <div class="col-6 col-md-4" data-aos="fade-up">
      <div class="card kpi-card p-3 hover-lift">
        <div class="d-flex align-items-center gap-3">
          <div class="kpi-icon kpi-upcoming"><i class="bi bi-calendar-event fs-5 text-primary"></i></div>
          <div><div class="text-muted small">Upcoming Bookings</div><div class="h3 mb-0"><?php echo $upcomingCount; ?></div></div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4" data-aos="fade-up" data-aos-delay="50">
      <div class="card kpi-card p-3 hover-lift">
        <div class="d-flex align-items-center gap-3">
          <div class="kpi-icon kpi-approved"><i class="bi bi-patch-check fs-5 text-success"></i></div>
          <div><div class="text-muted small">Approved This Month</div><div class="h3 mb-0"><?php echo $approvedThisMonth; ?></div></div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4" data-aos="fade-up" data-aos-delay="100">
      <div class="card kpi-card p-3 hover-lift">
        <div class="d-flex align-items-center gap-3">
          <div class="kpi-icon kpi-pending"><i class="bi bi-hourglass-split fs-5 text-warning"></i></div>
          <div><div class="text-muted small">Pending Requests</div><div class="h3 mb-0"><?php echo $pendingCount; ?></div></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-2">
    <div class="col-lg-7" data-aos="fade-right">
      <div class="card p-3 shadow-sm h-100 hover-lift">
        <h5 class="mb-2"><i class="bi bi-calendar-week me-2"></i>Upcoming Reservations</h5>
        <?php if($upcoming): ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead><tr><th>When</th><th>Room</th><th>Location</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
              <tbody>
                <?php foreach($upcoming as $r): ?>
                  <tr>
                    <td class="small"><?php echo h($r['date'].' '.substr($r['start_time'],0,5).'–'.substr($r['end_time'],0,5)); ?></td>
                    <td class="small"><span class="chip"><i class="bi bi-building me-1"></i><?php echo h($r['room_name']); ?></span></td>
                    <td class="small text-muted"><?php echo h($r['location']); ?></td>
                    <td class="small">
                      <?php $s=strtolower($r['status']); ?>
                      <span class="badge rounded-pill <?php echo $s==='approved'?'bg-success':($s==='pending'?'bg-warning text-dark':'bg-danger'); ?>">
                        <?php echo h(ucfirst($r['status'])); ?>
                      </span>
                    </td>
                    <td class="text-end">
                      <form class="d-inline" method="post" action="<?php echo h(base_url('../actions/reservation_cancel.php')); ?>" data-confirm="Cancel this reservation?">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle"></i></button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-muted small">No upcoming reservations.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- SYSTEM ANNOUNCEMENTS PANEL -->
    <div class="col-lg-5" data-aos="fade-left">
      <div class="card p-3 shadow-sm h-100 hover-lift">
        <h5 class="mb-2"><i class="bi bi-megaphone me-2"></i>System Announcements</h5>
        <?php if(!empty($announcements)): ?>
          <div class="list-group list-group-flush">
            <?php foreach($announcements as $a): ?>
              <?php
                $badge = ($a['severity']==='danger') ? 'bg-danger' : (($a['severity']==='warning') ? 'bg-warning text-dark' : 'bg-info text-dark');
                $dateLabel = $a['starts_at'] ?: $a['created_at'];
              ?>
              <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <span class="badge <?php echo $badge; ?> me-2"><?php echo h(ucfirst($a['severity'])); ?></span>
                    <strong><?php echo h($a['title']); ?></strong>
                  </div>
                  <small class="text-muted"><?php echo h(date('M j, Y', strtotime($dateLabel))); ?></small>
                </div>
                <div class="small mt-1 text-muted"><?php echo nl2br(h($a['body'])); ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-muted small">No announcements at the moment.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-1">
    <div class="col-12" data-aos="zoom-in">
      <div class="card p-3 shadow-sm hover-lift">
        <h5 class="mb-2"><i class="bi bi-clock-history me-2"></i>Recent History</h5>
        <?php if($history): ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead><tr><th>When</th><th>Room</th><th>Status</th><th>Purpose</th></tr></thead>
              <tbody>
                <?php foreach($history as $r): ?>
                  <tr>
                    <td class="small"><?php echo h($r['date'].' '.substr($r['start_time'],0,5).'–'.substr($r['end_time'],0,5)); ?></td>
                    <td class="small"><span class="chip"><i class="bi bi-building me-1"></i><?php echo h($r['room_name']); ?></span></td>
                    <td class="small">
                      <?php $s=strtolower($r['status']); ?>
                      <span class="badge rounded-pill <?php echo $s==='approved'?'bg-success':($s==='pending'?'bg-warning text-dark':($s==='canceled'?'bg-secondary':'bg-danger')); ?>">
                        <?php echo h(ucfirst($r['status'])); ?>
                      </span>
                    </td>
                    <td class="small text-truncate" style="max-width:300px;"><?php echo h($r['purpose']); ?></td>
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
