<?php
require_once __DIR__.'/../partials/head.php';
require_once __DIR__.'/../partials/nav.php';
require_role(['admin']);
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../lib/helpers.php';

// Pull newest first
$rows = $pdo->query("
  SELECT r.id, r.date, r.start_time, r.end_time, r.status, r.purpose,
         u.full_name, rm.name AS room_name
  FROM reservations r
  JOIN users u ON u.id = r.user_id
  JOIN rooms rm ON rm.id = r.room_id
  ORDER BY r.date DESC, r.start_time DESC, r.id DESC
")->fetchAll();
?>
<div class="container">
  <?php require __DIR__.'/../partials/flash.php'; ?>
  <div class="card card-shadow p-3" data-aos="fade-up">
    <h5 class="mb-2"><i class="bi bi-calendar3 me-1"></i>Reservations</h5>
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <th>Date</th>
            <th>Time</th>
            <th>Room</th>
            <th>User</th>
            <th>Purpose</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($rows as $r): ?>
            <?php
              $raw = strtolower(trim((string)$r['status']));
              // tolerate both spellings
              $s = ($raw === 'cancelled') ? 'canceled' : $raw;

              $badge = ($s==='approved') ? 'bg-success'
                     : (($s==='pending')  ? 'bg-warning text-dark'
                     : (($s==='canceled') ? 'bg-secondary'
                     : 'bg-danger')); // rejected/default

              $showActions = ($s === 'pending'); // only pending can be approved/rejected
            ?>
            <tr>
              <td><?php echo h($r['date']); ?></td>
              <td><?php echo h(substr($r['start_time'],0,5)); ?>–<?php echo h(substr($r['end_time'],0,5)); ?></td>
              <td><?php echo h($r['room_name']); ?></td>
              <td><?php echo h($r['full_name']); ?></td>
              <td class="small"><?php echo h($r['purpose']); ?></td>
              <td><span class="badge rounded-pill <?php echo $badge; ?>"><?php echo h(ucfirst($s)); ?></span></td>
              <td class="text-end">
                <?php if($showActions): ?>
                  <form class="d-inline" method="post" action="<?php echo h(base_url('../actions/reservation_update_status.php')); ?>" data-confirm="Approve this reservation?">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                    <input type="hidden" name="status" value="approved">
                    <input type="text" name="note" class="form-control form-control-sm d-inline-block w-auto" placeholder="Note (optional)">
                    <button class="btn btn-success btn-sm ms-1" title="Approve"><i class="bi bi-check2"></i></button>
                  </form>
                  <form class="d-inline" method="post" action="<?php echo h(base_url('../actions/reservation_update_status.php')); ?>" data-confirm="Reject this reservation?">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                    <input type="hidden" name="status" value="rejected">
                    <input type="text" name="note" class="form-control form-control-sm d-inline-block w-auto" placeholder="Reason">
                    <button class="btn btn-danger btn-sm ms-1" title="Reject"><i class="bi bi-x"></i></button>
                  </form>
                <?php else: ?>
                  <span class="text-muted small">No actions</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$rows): ?>
            <tr><td colspan="7" class="text-center text-muted">No reservations found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/../partials/footer.php'; ?>
