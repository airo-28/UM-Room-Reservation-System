<?php
require_once __DIR__.'/../partials/head.php';
require_once __DIR__.'/../partials/nav.php';
require_login();
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/csrf.php';

$u = user(); $uid = (int)$u['id'];

function qall($pdo,$sql,$args=[]){ $st=$pdo->prepare($sql); $st->execute($args); return $st->fetchAll(); }

$upcoming = qall($pdo, "
  SELECT r.id, r.date, r.start_time, r.end_time, r.status, r.purpose,
         rm.name AS room_name, rm.location
  FROM reservations r
  JOIN rooms rm ON rm.id=r.room_id
  WHERE r.user_id=? AND r.date>=CURDATE() AND r.status IN ('pending','approved')
  ORDER BY r.date ASC, r.start_time ASC
", [$uid]);

$history = qall($pdo, "
  SELECT r.id, r.date, r.start_time, r.end_time, r.status, r.purpose,
         rm.name AS room_name
  FROM reservations r
  JOIN rooms rm ON rm.id=r.room_id
  WHERE r.user_id=? AND (r.date < CURDATE() OR r.status IN ('rejected','canceled'))
  ORDER BY r.date DESC, r.start_time DESC
", [$uid]);
?>
<div class="container my-3">
  <?php require __DIR__.'/../partials/flash.php'; ?>

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <h5 class="mb-2"><i class="bi bi-calendar-week me-2"></i>Upcoming</h5>
      <?php if($upcoming): ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead><tr><th>When</th><th>Room</th><th>Location</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
              <?php foreach($upcoming as $r): ?>
                <tr>
                  <td class="small"><?php echo h($r['date'].' '.substr($r['start_time'],0,5).'–'.substr($r['end_time'],0,5)); ?></td>
                  <td class="small"><?php echo h($r['room_name']); ?></td>
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
                      <button class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle me-1"></i>Cancel</button>
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

  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="mb-2"><i class="bi bi-clock-history me-2"></i>History</h5>
      <?php if($history): ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead><tr><th>When</th><th>Room</th><th>Status</th><th>Purpose</th></tr></thead>
            <tbody>
              <?php foreach($history as $r): ?>
                <tr>
                  <td class="small"><?php echo h($r['date'].' '.substr($r['start_time'],0,5).'–'.substr($r['end_time'],0,5)); ?></td>
                  <td class="small"><?php echo h($r['room_name']); ?></td>
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
        <div class="text-muted small">No past reservations.</div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/../partials/footer.php'; ?>
