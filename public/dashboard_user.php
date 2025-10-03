<?php
require_once __DIR__.'/../partials/head.php';
require_once __DIR__.'/../partials/nav.php';
require_login();
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/csrf.php';

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
         rm.name AS room_name, rm.location, rm.capacity, COALESCE(rm.image_path,'') AS image_path, rm.type, COALESCE(rm.amenities,'') AS amenities, COALESCE(rm.description,'') AS description
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

$suggested = qall($pdo, "
  SELECT id, name, location, capacity, COALESCE(image_path,'') AS image_path, type, COALESCE(amenities,'') AS amenities, COALESCE(description,'') AS description
  FROM rooms
  ORDER BY name ASC
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
  .room-card img{aspect-ratio:16/9;object-fit:cover}
</style>

<div class="container my-3">
  <?php require __DIR__.'/../partials/flash.php'; ?>

  <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
    <h4 class="mb-2 mb-md-0">Welcome back, <?php echo h($u['name']); ?>!</h4>
    <div class="d-flex gap-2">
      <a class="btn btn-primary" href="<?php echo h(base_url('reservations.php')); ?>"><i class="bi bi-calendar-plus me-1"></i>New Reservation</a>
      <a class="btn btn-outline-secondary" href="<?php echo h(base_url('my_reservations.php')); ?>"><i class="bi bi-list-check me-1"></i>My Reservations</a>
      <a class="btn btn-outline-secondary" href="<?php echo h(base_url('rooms_gallery.php')); ?>"><i class="bi bi-collection me-1"></i>Browse Rooms</a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-6 col-md-4" data-aos="fade-up" data-aos-delay="0">
      <div class="card kpi-card p-3 hover-lift">
        <div class="d-flex align-items-center gap-3">
          <div class="kpi-icon kpi-upcoming"><i class="bi bi-calendar-event fs-5 text-primary"></i></div>
          <div>
            <div class="text-muted small">Upcoming Bookings</div>
            <div class="h3 mb-0"><?php echo $upcomingCount; ?></div>
          </div>
        </div>
        <div class="small text-muted mt-2">Next items shown below</div>
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
        <div class="small text-muted mt-2"><?php echo date('F Y'); ?></div>
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
        <div class="small mt-2"><a class="text-decoration-none" href="<?php echo h(base_url('my_reservations.php')); ?>">View details</a></div>
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
        <?php if($upcoming && count($upcoming)>0): ?>
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
          <div class="text-muted small">You have no upcoming reservations.</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-lg-5" data-aos="fade-left">
      <div class="card p-3 shadow-sm h-100 hover-lift">
        <h5 class="mb-2"><i class="bi bi-collection me-2"></i>Popular Rooms</h5>
        <div class="row g-3">
          <?php if($suggested && count($suggested)>0): ?>
            <?php foreach($suggested as $rm): ?>
              <div class="col-12">
                <div class="card room-card hover-lift">
                  <?php $img = $rm['image_path'] ?: base_url('../assets/img/rooms/room'.(($rm['id']%4)+1).'.jpg'); ?>
                  <img src="<?php echo h($img); ?>" class="w-100" alt="room">
                  <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center">
                      <strong><?php echo h($rm['name']); ?></strong>
                      <span class="badge bg-light text-dark">Cap <?php echo (int)$rm['capacity']; ?></span>
                    </div>
                    <div class="small text-muted mb-1"><?php echo h($rm['location']); ?> • <?php echo h($rm['type']); ?></div>
                    <?php if(!empty($rm['amenities'])): ?><div class="small"><span class="chip"><i class="bi bi-stars me-1"></i><?php echo h($rm['amenities']); ?></span></div><?php endif; ?>
                    <?php if(!empty($rm['description'])): ?><div class="small text-muted mt-1"><?php echo h($rm['description']); ?></div><?php endif; ?>
                    <div class="mt-2 d-flex gap-2">
                      <a class="btn btn-sm btn-primary" href="<?php echo h(base_url('reservations.php')); ?>"><i class="bi bi-calendar-plus me-1"></i>Book</a>
                      <a class="btn btn-sm btn-outline-secondary" href="<?php echo h(base_url('rooms_gallery.php')); ?>"><i class="bi bi-images me-1"></i>View</a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-muted small px-3">No rooms available.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-1">
    <div class="col-12" data-aos="zoom-in">
      <div class="card p-3 shadow-sm hover-lift">
        <h5 class="mb-2"><i class="bi bi-clock-history me-2"></i>Recent History</h5>
        <?php if($history && count($history)>0): ?>
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
